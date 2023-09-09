<?php

namespace App\Services;

use App\BytePair\Encoder;
use Illuminate\Support\Facades\Cache;
use Mustache_Engine;
use OpenAI\Client;
use OpenAI\Factory;
use ReflectionClass;

class AIService
{
    protected Client $client;
    protected Mustache_Engine $mustache;
    private Encoder $encoder;
    private $functions = [];

    public function __construct(protected string $apiKey)
    {
        $this->client = self::factory()
            ->withApiKey($apiKey)
            ->make();
        $this->mustache = new Mustache_Engine([
            'escape' => function ($value) {
                return json_encode($value);
            },
        ]);
        $this->encoder = new Encoder();
    }

    private static function factory(): Factory
    {
        return new Factory();
    }

    public function countTokens($prompt)
    {
        return count($this->encoder->encode($prompt));
    }

    public function complete(string $prompt, array $data, $config = ['stop' => '```'])
    {
        $model = 'text-davinci-003';
        $prompt = $this->mustache->render($prompt, $data);
        $promptTokens = $this->countTokens($prompt);
        // text-davinci-003 top limit of tokens
        $maxTokens = 4000 - $promptTokens;
        $result = $this->client->completions()->create([
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => $maxTokens,
            ...$config,
        ]);

        return $result['choices'][0]['text'];
    }

    public function chatWithFunctions(array $chat, array $data, array $functions): array
    {
        $model = 'gpt-4-0613'; // with function calling
        //$model = 'gpt-3.5-turbo-0613';

        // Render the chat messages with the provided data using Mustache
        $renderedChat = array_map(function ($message) use ($data) {
            $message['content'] = $this->mustache->render($message['content'], $data);
            return $message;
        }, $chat);

        // Implement a cache by $renderedChat
        $md5 = md5(json_encode($renderedChat));
        return Cache::remember($md5, 60, function () use ($chat, $data, $functions, $model, $renderedChat) {
            // Make a call to the OpenAI client's chat completion endpoint
            $result = $this->client->chat()->create([
                'model' => $model,
                'messages' => $renderedChat,
                'functions' => $functions,
                'function_call' => 'auto',
            ]);

            // Return the model's response
            $response = $result['choices'][0]['message'];
            if (isset($response['function_call']) && isset($this->functions[$response['function_call']['name']])) {
                $arguments = json_decode($response['function_call']['arguments'], true);
                $function = $this->functions[$response['function_call']['name']];
                error_log($response['function_call']['name']);
                $chat[] = $response;
                $chat[] = [
                    'role' => 'function',
                    'name' => $response['function_call']['name'],
                    'content' => $function($arguments),
                ];
                return $this->chatWithFunctions($chat, $data, $functions);
            }
            return $response;
        });
    }

    public function registerFunction($name, callable $callback)
    {
        $this->functions[$name] = $callback;
    }

    public function prepareObjectDescription($object)
    {
        $reflectedClass = new ReflectionClass($object);
        $methods = $reflectedClass->getMethods();
        $methodsDetails = [];

        foreach ($methods as $method) {
            $docComment = $method->getDocComment();

            $methodName = $method->getName();
            $methodDescription = "";
            $parametersDetails = [];

            if ($docComment === false) {
                continue;
            }
            // Get method description
            $methodDescription = trim(substr(trim(explode("\n", $docComment)[1]), 1));
            $required = [];

            // Get parameters details
            foreach ($method->getParameters() as $parameter) {
                $paramName = $parameter->getName();
                $paramType = $parameter->hasType() ? $parameter->getType()->getName() : "string";
                $paramDescription = "";
                $isRequired = !$parameter->isOptional();
                if ($isRequired) {
                    $required[] = $paramName;
                }

                // Extracting parameter description from doc comment
                preg_match('/@param\s+(\S+)\s+\$' . $paramName . '\s+(.+)/', $docComment, $paramMatches);
                if (isset($paramMatches[2])) {
                    $paramDescription = trim($paramMatches[2]);
                }

                $parametersDetails[$paramName] = [
                    "type" => $paramType,
                    "description" => $paramDescription
                ];
            }

            $methodsDetails[] = [
                "name" => $methodName,
                "description" => $methodDescription,
                "parameters" => [
                    'type' => 'object',
                    'properties' => (object) $parametersDetails,
                    'required' => $required,
                ],
            ];
        }

        return $methodsDetails;
    }
}
