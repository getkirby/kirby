<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class KirbyTagsTest extends TestCase
{

    public function dataProvider()
    {
        $tests = [];

        foreach(Dir::read($root = __DIR__ . '/fixtures/kirbytext') as $dir) {
            $kirbytext = F::read($root . '/' . $dir . '/test.txt');
            $expected  = F::read($root . '/' . $dir . '/expected.html');

            $tests[] = [trim($kirbytext), trim($expected)];
        }

        return $tests;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testWithMarkdown($kirbytext, $expected)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'markdown' => [
                    'extra' => false
                ]
            ]
        ]);

        $this->assertEquals($expected, $kirby->kirbytext($kirbytext));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testWithMarkdownExtra($kirbytext, $expected)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'markdown' => [
                    'extra' => true
                ]
            ]
        ]);

        $this->assertEquals($expected, $kirby->kirbytext($kirbytext));
    }

    public function testHooks()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'hooks' => [
                'kirbytags:before' => function ($text, $data, $options) {
                    return 'before';
                },
            ]
        ]);

        $this->assertEquals('before', $app->kirbytags('test'));

        $app = $app->clone([
            'hooks' => [
                'kirbytags:after' => function ($text, $data, $options) {
                    return 'after';
                },
            ]
        ]);

        $this->assertEquals('after', $app->kirbytags('test'));
    }

}
