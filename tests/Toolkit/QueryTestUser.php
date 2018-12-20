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
        return implode(' ', $message);
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
}
