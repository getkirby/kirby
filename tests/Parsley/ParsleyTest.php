<?php

namespace Kirby\Parsley;

use Kirby\Parsley\Schema\Blocks;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class ParsleyTest extends TestCase
{
    public function testParsley()
    {
        $examples = glob(__DIR__ . '/fixtures/*.html');

        foreach ($examples as $example) {
            $input    = F::read($example);
            $expected = require_once dirname($example) . '/' . F::name($example) . '.php';

            $parser = new Parsley($input, new Blocks());
            $output = $parser->blocks();

            $this->assertEquals($expected, $output, basename($example));
        }
    }
}
