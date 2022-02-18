<?php

namespace Kirby\Cms;

use Kirby\Email\Email;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class CustomEmailProvider extends Email
{
    public static $apiKey;

    public function __construct(array $props = [], bool $debug = false)
    {
        parent::__construct($props, $debug);
    }

    public function send(bool $debug = false): bool
    {
        static::$apiKey = 'KIRBY';
        return true;
    }
}

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
        $this->assertSame($expected, css('something.css'));
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

        $this->assertSame('test', dump('test'));
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
        $this->assertSame($expected, js('something.js'));
    }

    public function testKirbytext()
    {
        $text     = 'Test';
        $expected = '<p>Test</p>';

        $this->assertEquals($expected, $this->kirby->kirbytext($text));
    }

    public function testKirbytextWithSafeMode()
    {
        $text     = '<h1>**Test**</h1>';
        $expected = '&lt;h1&gt;<strong>Test</strong>&lt;/h1&gt;';

        $this->assertEquals($expected, $this->kirby->kirbytext($text, [
            'markdown' => [
                'safe' => true
            ]
        ], true));
    }

    public function testKirbytextInline()
    {
        $text     = 'Test';
        $expected = 'Test';

        $this->assertEquals($expected, $this->kirby->kirbytext($text, [
            'markdown' => [
                'inline' => true
            ]
        ], true));
    }

    public function testKirbytextInlineDeprecated()
    {
        $text     = 'Test';
        $expected = 'Test';

        $this->assertEquals($expected, $this->kirby->kirbytext($text, [], true));
    }

    public function testMarkdown()
    {
        $text     = 'Test';
        $expected = '<p>Test</p>';

        $this->assertSame($expected, $this->kirby->markdown($text));
    }

    public function testMarkdownInline()
    {
        $text     = 'Test';
        $expected = 'Test';

        $this->assertEquals($expected, $this->kirby->markdown($text, ['inline' => true]));

        // deprecated boolean second option
        $this->assertEquals($expected, $this->kirby->markdown($text, true));

        // deprecated fourth argument
        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, [], true));
    }

    public function testMarkdownWithSafeMode()
    {
        $text     = '<div>Test</div>';
        $expected = '<p>&lt;div&gt;Test&lt;/div&gt;</p>';

        $this->assertEquals($expected, $this->kirby->markdown($text, ['safe' => true]));
    }

    public function testMarkdownCachedInstance()
    {
        $text     = '1st line
2nd line';
        $expected = '<p>1st line<br />
2nd line</p>';

        $this->assertSame($expected, $this->kirby->component('markdown')($this->kirby, $text, []));

        $expected = '<p>1st line
2nd line</p>';
        $this->assertSame($expected, $this->kirby->component('markdown')($this->kirby, $text, ['breaks' => false]));
    }

    public function testMarkdownPlugin()
    {
        $this->kirby = $this->kirby->clone([
            'components' => [
                'markdown' => function (App $kirby, string $text = null, array $options = [], bool $inline = false) {
                    $result = Html::encode($text);

                    if (!$inline) {
                        $result = '<p>' . $result . '</p>';
                    }

                    return '<pre><code>' . $result . '</pre></code>';
                }
            ]
        ]);

        $text     = 'Test _case_';

        $expected = '<pre><code><p>Test _case_</p></pre></code>';
        $this->assertEquals($expected, $this->kirby->markdown($text));

        // deprecated boolean second option
        $this->assertEquals($expected, $this->kirby->markdown($text, false));

        // deprecated fourth argument
        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, [], false));

        $expected = '<pre><code>Test _case_</pre></code>';
        $this->assertEquals($expected, $this->kirby->markdown($text, ['inline' => true]));

        // deprecated boolean second option
        $this->assertEquals($expected, $this->kirby->markdown($text, true));

        // deprecated fourth argument
        $this->assertEquals($expected, $this->kirby->component('markdown')($this->kirby, $text, [], true));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertSame($expected, $this->kirby->smartypants($text));
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

        $this->assertSame($expected, $this->kirby->component('smartypants')($this->kirby, $text, []));

        $expected = 'TestTest&#8221;';
        $this->assertSame($expected, $this->kirby->component('smartypants')($this->kirby, $text, ['doublequote.open' => 'Test']));
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
        $this->assertSame('test', $app->snippet('test'));

        // field
        $this->assertSame('test', $app->snippet(new Field(null, 'test', 'test')));

        // fallback
        $this->assertSame('fallback', $app->snippet(['does-not-exist', 'fallback']));

        // fallback from field
        $this->assertSame('fallback', $app->snippet(['does-not-exist', new Field(null, 'test', 'fallback')]));

        // from plugin
        $this->assertSame('plugin', $app->snippet('plugin'));

        // from plugin with field
        $this->assertSame('plugin', $app->snippet(new Field(null, 'test', 'plugin')));

        // fallback from plugin
        $this->assertSame('plugin', $app->snippet(['does-not-exist', 'plugin']));

        // fallback from plugin with field
        $this->assertSame('plugin', $app->snippet(['does-not-exist', new Field(null, 'test', 'plugin') ]));

        // inject data
        $this->assertSame('test', $app->snippet('variable', ['message' => 'test']));

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

        $this->assertSame('test', url('anything'));
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

        $this->assertSame('test-path', url('test'));
        $this->assertSame('/any/page', url('any/page'));
    }

    public function testEmail()
    {
        $app = $this->kirby->clone([
            'components' => [
                'email' => function ($kirby, $props, $debug) {
                    return new CustomEmailProvider($props, $debug);
                }
            ]
        ]);

        $email = $app->email([
            'from' => 'no-reply@supercompany.com',
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'body' => 'We will never reply'
        ], ['debug' => true]);

        $this->assertInstanceOf(CustomEmailProvider::class, $email);
        $this->assertTrue(property_exists($email, 'apiKey'));
        $this->assertSame('no-reply@supercompany.com', $email->from());
        $this->assertSame(['someone@gmail.com' => null], $email->to());
        $this->assertSame('Thank you for your contact request', $email->subject());
        $this->assertSame('We will never reply', $email->body()->text());

        $this->assertNull($email::$apiKey);
        $email->send();
        $this->assertSame('KIRBY', $email::$apiKey);
    }
}
