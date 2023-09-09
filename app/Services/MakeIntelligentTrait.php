<?php

namespace App\Services;

trait MakeIntelligentTrait
{
    public function ai($message)
    {
        $chat = [
            [
                'role' => 'user',
                'content' => $message,
            ],
        ];
        $service = new AIService(env('OPENAI_API_KEY'));
        $methods = $service->prepareObjectDescription($this);
        foreach($methods as $method) {
            $service->registerFunction($method['name'], function ($arguments) use ($method) {
                $args = [];
                foreach($method['parameters']['properties'] as $name => $details) {
                    $args[] = $arguments[$name] ?? null;
                }
                $response = $this->{$method['name']}(...$args);
                return is_string($response) ? $response : json_encode($response);
            });
        }
        $result = $service->chatWithFunctions($chat, (array) $this, $methods);
        return $result;
    }
    public function aiCode($message, $language)
    {
        $result = $this->ai($message);
        $content = $result['content'];
        // find ```language block
        $start = strpos($content, "```$language");
        if ($start === false) {
            $language = '';
            $start = strpos($content, "```");
        }
        if ($start === false) {
            return $content;
        }
        // find last ```
        $end = strrpos($content, '```');
        if ($end === false) {
            return $content;
        }
        if ($start > $end) {
            return substr($content, $start + strlen($language) + 3);
        }
        return substr($content, $start + strlen($language) + 3, $end - $start - strlen($language) - 3);
    }

    public function __invoke($message)
    {
        $prompt = "escribe una lista simple con los pasos (cada paso no debe tener más de 15 palabras) para: " . $message;
        $result = $this->ai($prompt);
        $plan = $result['content'];
        $prompt = $message . "\nSigue los siguientes pasos:\n" . $plan;
        $result = $this->ai($prompt);
        return $result['content'];
    }
}