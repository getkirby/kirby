<?php

namespace Kirby\Email;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function _email(array $props, $mailer)
    {
        return new $mailer(array_merge([
            'from' => 'no-reply@supercompany.com',
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'body' => 'We will never reply'
        ], $props), true);
    }
}
