<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use PHPUnit\Framework\TestCase;

class CustomPanelModel extends Model
{
    public function path(): string
    {
        return 'custom';
    }

    public function route(): array
    {
        return [];
    }
}

class ModelSiteTestForceLocked extends ModelSite
{
    public function isLocked(): bool
    {
        return true;
    }
}

/**
 * @coversDefaultClass \Kirby\Panel\Model
 */
class ModelTest extends TestCase
{
    protected function panel(array $props = [])
    {
        $site = new ModelSite($props);
        return new CustomPanelModel($site);
    }

    /**
     * @covers ::__construct
     * @covers ::content
     */
    public function testContent()
    {
        $panel = $this->panel([
            'content' => $content = [
                'foo' => 'bar'
            ]
        ]);
        $this->assertSame($content, $panel->content());
    }

    /**
     * @covers ::dragTextFromCallback
     */
    public function testDragTextFromCallbackMarkdown()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],

            'options' => [
                'panel' => [
                    'markdown' => [
                        'fileDragText' => function (\Kirby\Cms\File $file, string $url) {
                            if ($file->extension() === 'heic') {
                                return sprintf('![](%s)', $file->id());
                            }

                            return null;
                        },
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.heic'],
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $file  = $app->page('test')->file('test.jpg');
        $panel = new CustomPanelModel($file);
        $this->assertSame(null, $panel->dragTextFromCallback('markdown', $file, $file->filename()));

        // Custom function should return image tag for heic
        $file  = $app->page('test')->file('test.heic');
        $panel = new CustomPanelModel($file);
        $this->assertSame('![](test/test.heic)', $panel->dragTextFromCallback('markdown', $file, $file->filename()));
    }

    /**
     * @covers ::dragTextFromCallback
     */
    public function testDragTextFromCallbackKirbytext()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],

            'options' => [
                'panel' => [
                    'kirbytext' => [
                        'fileDragText' => function (\Kirby\Cms\File $file, string $url) {
                            if ($file->extension() === 'heic') {
                                return sprintf('(image: %s)', $file->id());
                            }

                            return null;
                        },
                    ]
                ]
            ],

            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.heic'],
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $file  = $app->page('test')->file('test.jpg');
        $panel = new CustomPanelModel($file);
        $this->assertSame(null, $panel->dragTextFromCallback('kirbytext', $file, $file->filename()));

        // Custom function should return image tag for heic
        $file  = $app->page('test')->file('test.heic');
        $panel = new CustomPanelModel($file);
        $this->assertSame('(image: test/test.heic)', $panel->dragTextFromCallback('kirbytext', $file, $file->filename()));
    }

    /**
     * @covers ::dragTextType
     */
    public function testDragTextType()
    {
        $panel = $this->panel();

        // with passed value
        $this->assertSame('markdown', $panel->dragTextType('markdown'));
        $this->assertSame('kirbytext', $panel->dragTextType('kirbytext'));
        $this->assertSame('kirbytext', $panel->dragTextType('foo'));

        // auto
        $this->assertSame('kirbytext', $panel->dragTextType());

        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => ['panel' => ['kirbytext' => false]]
        ]);
        $this->assertSame('markdown', $panel->dragTextType());

        // reset app instance
        new App();
    }

    /**
     * @covers ::icon
     */
    public function testIcon()
    {
        $panel = $this->panel();

        $icon  = $panel->icon();
        $this->assertTrue(isset($icon['type']));
        $this->assertTrue(isset($icon['back']));
        $this->assertTrue(isset($icon['color']));
        $this->assertSame('page', $icon['type']);
        $this->assertSame('pattern', $icon['back']);

        $icon  = $panel->icon([
            'type'  => $type = 'heart',
            'ratio' => $ratio = '16/9'
        ]);
        $this->assertSame($type, $icon['type']);
        $this->assertSame($ratio, $icon['ratio']);
    }

    /**
     * @covers ::image
     * @covers ::imageSource
     */
    public function testImage()
    {
        $panel = $this->panel([
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        // defaults
        $image = $panel->image();
        $this->assertTrue(isset($image['ratio']));
        $this->assertTrue(isset($image['back']));
        $this->assertTrue(isset($image['cover']));
        $this->assertSame('3/2', $image['ratio']);
        $this->assertSame(false, $image['cover']);

        // deactivate
        $this->assertNull($panel->image(false));

        // icon
        $this->assertSame([], $panel->image('icon'));

        // invalid query
        $image = $panel->image('site.foo');
        $this->assertFalse(isset($image['url']));
        $this->assertFalse(isset($image['query']));

        // valid query
        $image = $panel->image('site.image');
        $this->assertTrue(isset($image['url']));
        $this->assertTrue(isset($image['cards']));
        $this->assertTrue(isset($image['list']));
        $this->assertFalse(isset($image['query']));

        // full options
        $image = $panel->image([
            'ratio' => '16/9',
            'query' => 'site.image',
            'cover' => true
        ]);
        $this->assertTrue(isset($image['url']));
        $this->assertTrue(isset($image['cards']));
        $this->assertTrue(isset($image['list']));
        $this->assertSame('16/9', $image['ratio']);
    }

    /**
     * @covers ::prevnext
     */
    public function testPrevNext()
    {
        $panel = $this->panel([
            'content' => [
                'title'  => $title = 'Kirby Kirby Kirby',
                'author' => $author = 'Bastian Allgeier'
            ]
        ]);

        $prevnext = $panel->prevnext();
        $this->assertSame('/custom', $prevnext['link']);
        $this->assertSame($title, $prevnext['tooltip']);

        $prevnext = $panel->prevnext('author');
        $this->assertSame('/custom', $prevnext['link']);
        $this->assertSame($author, $prevnext['tooltip']);
    }

    /**
     * @covers ::props
     */
    public function testProps()
    {
        $site = [
            'blueprint' => [
                'name'    => 'site',
                'columns' => [
                    [
                        'width'    => '1/3',
                        'sections' => []
                    ],
                    [
                        'width'    => '2/3',
                        'sections' => []
                    ]
                ]
            ]
        ];

        $props = $this->panel($site)->props();
        $this->assertSame('site', $props['blueprint']);
        $this->assertSame('main', $props['tabs'][0]['name']);
        $this->assertSame('main', $props['tab']['name']);
        $this->assertFalse($props['permissions']['update']);

        new App([
            'request' => [
                'url' => [
                    'params' => 'tab:foo'
                ]
            ]
        ]);

        $props = $this->panel($site)->props();
        $this->assertSame('foo', param('tab'));
        $this->assertSame('main', $props['tab']['name']);
    }

    /**
     * @covers ::url
     */
    public function testUrl()
    {
        $this->assertSame('/panel/custom', $this->panel()->url());
        $this->assertSame('/custom', $this->panel()->url(true));
    }
}
