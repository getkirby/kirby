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

            $this->assertEquals($output, $expected, basename($example));
        }
    }

    public function testMissingXmlExtension()
    {
        Parsley::$documentClass = 'DOMDocumentDoesNotExist';

        $parser   = new Parsley('Test', new Blocks());
        $output   = $parser->blocks();
        $expected = [
            [
                'type' => 'markdown',
                'content' => [
                    'text' => 'Test'
                ]
            ]
        ];

        $this->assertEquals($output, $expected);

        // revert the global change
        Parsley::$documentClass = 'DOMDocument';
    }
}
