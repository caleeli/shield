<?php

namespace App\BytePair;

class Encoder
{

    private $encoder;
    private $lines;
    private $decoder;
    private $bpe_merges;
    private $byte_encoder;
    private $cache = [];
    private $pat = "/'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/u";

    public function __construct()
    {
        $this->encoder = json_decode(file_get_contents(__DIR__ . '/encoder.json'), true);
        $this->lines = explode("\n", file_get_contents(__DIR__ . '/vocab.txt'));
        $this->setupDecoder();
        $this->setupBpeMerges();
        $this->byte_encoder = $this->bytes_to_unicode();
        $this->bpe_merges = array_slice($this->lines, 1, -1);
        $this->bpe_merges = array_map(function($x) {
            return preg_split('/\s+/u', trim($x));
        }, $this->bpe_merges);
    }

    private function rangeCustom($x, $y)
    {
        return array_slice(range(0, $y - 1), $x);
    }

    private function setupDecoder()
    {
        foreach ($this->encoder as $key => $value) {
            $this->decoder[$value] = $key;
        }
    }

    private function setupBpeMerges()
    {
        $this->bpe_merges = array_slice($this->lines, 1, -1);
        $this->bpe_merges = array_map(function ($x) {
            return preg_split('/\s+/u', trim($x));
        }, $this->bpe_merges);
    }

    private function bytes_to_unicode()
    {
        $bs = array_merge(
            $this->rangeCustom(ord('!'), ord('~') + 1),
            $this->rangeCustom(ord('¡'), ord('¬') + 1),
            $this->rangeCustom(ord('®'), ord('ÿ') + 1)
        );

        $cs = array_slice($bs, 0);
        $n = 0;
        for ($b = 0; $b < 2 ** 8; $b++) {
            if (!in_array($b, $bs)) {
                $bs[] = $b;
                $cs[] = 2 ** 8 + $n;
                $n++;
            }
        }
        return $this->dictZip($bs, array_map('mb_chr', $cs));
    }

    private function dictZip($x, $y)
    {
        return array_combine($x, $y);
    }

    private function get_pairs(array $word)
    {
        $pairs = [];
        $prev_char = $word[0];
        for ($i = 1; $i < count($word); $i++) {
            $char = $word[$i];
            $pairs[] = [$prev_char, $char];
            $prev_char = $char;
        }
        return $pairs;
    }

    private function bpe($token)
    {
        if (isset($this->cache[$token])) {
            return $this->cache[$token];
        }

        $word = mb_str_split($token);

        $pairs = $this->get_pairs($word);

        if (!$pairs) {
            return $token;
        }

        while (true) {
            $minPairs = [];
            foreach ($pairs as $pair) {
                $rank = array_search($pair, $this->bpe_merges);
                if ($rank === false) {
                    $rank = 10e10;
                }
                $minPairs[$rank] = $pair;
            }

            $bigram = $minPairs[min(array_keys($minPairs))];

            if (array_search($bigram, $this->bpe_merges) === false) {
                break;
            }

            list($first, $second) = $bigram;
            $new_word = [];
            $i = 0;

            while ($i < count($word)) {
                $j = $this->indexOf($first, $word, $i);

                if ($j === false) {
                    $new_word = array_merge($new_word, array_slice($word, $i));
                    break;
                }

                $new_word = array_merge($new_word, array_slice($word, $i, $j - $i));
                $i = $j;

                if ($word[$i] == $first && $i < count($word) - 1 && $word[$i + 1] == $second) {
                    $new_word[] = $first . $second;
                    $i += 2;
                } else {
                    $new_word[] = $word[$i];
                    $i++;
                }
            }

            $word = $new_word;

            if (count($word) === 1) {
                break;
            } else {
                $pairs = $this->get_pairs($word);
            }
        }

        $this->cache[$token] = $word = implode(' ', $word);
        return $word;
    }

    private function indexOf($item, $array, $offset = 0) {
        // Check if offset is valid
        if ($offset < 0 || $offset >= count($array)) {
            return false;
        }
    
        // Start searching from the offset
        for ($i = $offset; $i < count($array); $i++) {
            if ($array[$i] == $item) {
                return $i; // Return the index if item is found
            }
        }
    
        return false; // Return false if item is not found
    }

    private function encodeStr($str)
    {
        return array_map(function ($x) {
            return mb_ord($x);
        }, mb_str_split($str));
    }

    public function encode($text)
    {
        preg_match_all($this->pat, $text, $matches);
        $matches = $matches[0];

        $bpe_tokens = [];
        foreach ($matches as $token) {
            $token = implode('', array_map(function ($x) {
                return $this->byte_encoder[$x];
            }, $this->encodeStr($token)));
            $new_tokens = array_map(function ($x) {
                return $this->encoder[$x];
            }, explode(' ', $this->bpe($token)));

            $bpe_tokens = array_merge($bpe_tokens, $new_tokens);
        }
        return $bpe_tokens;
    }
}
