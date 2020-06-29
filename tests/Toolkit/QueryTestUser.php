<?php

namespace Kirby\Toolkit;

class QueryTestUser
{
    public function username()
    {
        return 'homer';
    }

    public function profiles()
    {
        return [
            'twitter' => '@homer'
        ];
    }

    public function says(...$message)
    {
        return implode(' : ', $message);
    }

    public function age(int $years)
    {
        return $years;
    }

    public function isYello(bool $answer)
    {
        return $answer;
    }

    public function brainDump($dump)
    {
        return $dump;
    }

    public function array(...$args)
    {
        return ['args' => $args];
    }

    public function check($needle1, $needle2, $array)
    {
        return in_array($needle1, $array) && in_array($needle2, $array);
    }

    public function drink()
    {
        return ['gin', 'tonic', 'cucumber'];
    }

    public function self()
    {
        return $this;
    }

    public function likes(array $arguments)
    {
        foreach ($arguments as $arg) {
            if (in_array($arg, ['(', ')', ',', ']', '[']) === false) {
                throw new \Exception();
            }
        }

        return $this;
    }

    public function nothing()
    {
        return null;
    }
}
