<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class AppComponentsTest extends TestCase
{
    protected $kirby;

    public function setUp(): void
    {
        $this->kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testCssPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'css' => function ($kirby, $url, $options) {
                    return '/test.css';
                }
            ]
        ]);

        $expected = '<link href="/test.css" rel="stylesheet">';
        $this->assertEquals($expected, css('something.css'));
    }

    public function testDump()
    {
        $kirby = $this->kirby->clone([
            'components' => [
                'dump' => function ($kirby, $variable) {
                    return $variable;
                }
            ]
        ]);

        $this->assertEquals('test', dump('test'));
    }

    public function testJsPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'js' => function ($kirby, $url, $options) {
                    return '/test.js';
                }
            ]
        ]);

        $expected = '<script src="/test.js"></script>';
        $this->assertEquals($expected, js('something.js'));
    }

    public function testMarkdown()
    {
        $text     = 'Test';
        $expected = '<p>Test</p>';

        $this->assertEquals($expected, $this->kirby->markdown($text));
    }

    public function testMarkdownCachedInstance()
    {
        $text     = '1st line
2nd line';
        $expected = '<p>1st line<br />
2nd line</p>';

        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, []));

        $expected = '<p>1st line
2nd line</p>';
        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, ['breaks' => false]));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsDisabled()
    {
        $this->kirby = $this->kirby->clone([
            'options' => [
                'smartypants'   => false
            ]
        ]);

        $text     = '"Test"';
        $expected = '"Test"';

        $this->assertSame($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsOptions()
    {
        $this->kirby = $this->kirby->clone([
            'options' => [
                'languages'   => true,
                'smartypants' => [
                    'doublequote.open'  => '<',
                    'doublequote.close' => '>'
                ]
            ]
        ]);

        $text     = '"Test"';
        $expected = '<Test>';

        $this->assertSame($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsMultiLang()
    {
        $this->kirby = $this->kirby->clone([
            'options' => [
                'languages'     => true,
                'smartypants'   => true
            ],
            'languages' => [
                [
                    'code'          => 'en',
                    'name'          => 'English',
                    'default'       => true,
                    'locale'        => 'en_US',
                    'url'           => '/',
                    'smartypants'   => [
                        'doublequote.open'  => '<',
                        'doublequote.close' => '>'
                    ]
                ],
                [
                    'code'          => 'de',
                    'name'          => 'Deutsch',
                    'locale'        => 'de_DE',
                    'url'           => '/de',
                    'smartypants'   => [
                        'doublequote.open'  => '<<',
                        'doublequote.close' => '>>'
                    ]
                ]
            ]
        ]);

        $text     = '"Test"';
        $expected = '<Test>';

        $this->assertSame($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsDefaultOptionsOnMultiLang()
    {
        $this->kirby = $this->kirby->clone([
            'options' => [
                'languages'     => true,
                'smartypants'   => true
            ],
            'languages' => [
                [
                    'code'          => 'en',
                    'name'          => 'English',
                    'default'       => true,
                    'locale'        => 'en_US',
                    'url'           => '/'
                ],
                [
                    'code'          => 'de',
                    'name'          => 'Deutsch',
                    'locale'        => 'de_DE',
                    'url'           => '/de'
                ]
            ]
        ]);

        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertSame($expected, $this->kirby->smartypants($text));
    }

    public function testSmartypantsCachedInstance()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->kirby->component('smartypants')($this->kirby, $text, []));

        $expected = 'TestTest&#8221;';
        $this->assertEquals($expected, $this->kirby->component('smartypants')($this->kirby, $text, ['doublequote.open' => 'Test']));
    }

    public function testSnippet()
    {
        $app = $this->kirby->clone([
            'roots' => [
                'snippets' => $fixtures = __DIR__ . '/fixtures/snippets'
            ],
            'snippets' => [
                'plugin' => $fixtures . '/plugin.php'
            ]
        ]);

        F::write($fixtures . '/variable.php', '<?= $message;');
        F::write($fixtures . '/test.php', 'test');
        F::write($fixtures . '/fallback.php', 'fallback');
        F::write($fixtures . '/plugin.php', 'plugin');

        // simple string
        $this->assertEquals('test', $app->snippet('test'));

        // field
        $this->assertEquals('test', $app->snippet(new Field(null, 'test', 'test')));

        // fallback
        $this->assertEquals('fallback', $app->snippet(['does-not-exist', 'fallback']));

        // fallback from field
        $this->assertEquals('fallback', $app->snippet(['does-not-exist', new Field(null, 'test', 'fallback')]));

        // from plugin
        $this->assertEquals('plugin', $app->snippet('plugin'));

        // from plugin with field
        $this->assertEquals('plugin', $app->snippet(new Field(null, 'test', 'plugin')));

        // fallback from plugin
        $this->assertEquals('plugin', $app->snippet(['does-not-exist', 'plugin']));

        // fallback from plugin with field
        $this->assertEquals('plugin', $app->snippet(['does-not-exist', new Field(null, 'test', 'plugin') ]));

        // inject data
        $this->assertEquals('test', $app->snippet('variable', ['message' => 'test']));

        Dir::remove($fixtures);
    }

    public function testTemplate()
    {
        $this->assertInstanceOf(Template::class, $this->kirby->template('default'));
    }

    public function testUrlPlugin()
    {
        $this->kirby->clone([
            'components' => [
                'url' => function ($kirby, $path, $options) {
                    return 'test';
                }
            ]
        ]);

        $this->assertEquals('test', url('anything'));
    }

    public function testUrlPluginWithOriginalHandler()
    {
        $this->kirby->clone([
            'components' => [
                'url' => function ($kirby, $path, $options, $originalHandler) {
                    if ($path === 'test') {
                        return 'test-path';
                    }

                    return $originalHandler($path);
                }
            ]
        ]);

        $this->assertEquals('test-path', url('test'));
        $this->assertEquals('/any/page', url('any/page'));
    }

    public function testUrlPluginWithNativeComponent()
    {
        $this->kirby->clone([
            'components' => [
                'url' => function ($kirby, $path, $options) {
                    if ($path === 'test') {
                        return 'test-path';
                    }

                    return $kirby->nativeComponent('url')($kirby, $path, $options);
                }
            ]
        ]);

        $this->assertEquals('test-path', url('test'));
        $this->assertEquals('/any/page', url('any/page'));
    }
}
