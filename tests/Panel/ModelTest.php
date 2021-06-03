<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class CustomContentLockIsLocked
{
    public function get(): array
    {
        return ['email' => 'foo@bar.com'];
    }

    public function isLocked(): bool
    {
        return true;
    }

    public function isUnlocked(): bool
    {
        return false;
    }
}

class CustomContentLockIsUnlocked
{
    public function isUnlocked(): bool
    {
        return true;
    }
}

class ModelSiteNoLocking extends ModelSite
{
    public function lock()
    {
    }
}

class ModelSiteTestForceLocked extends ModelSite
{
    public function lock()
    {
        return new CustomContentLockIsLocked();
    }
}

class ModelSiteTestForceUnlocked extends ModelSite
{
    public function lock()
    {
        return new CustomContentLockIsUnlocked();
    }
}

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

/**
 * @coversDefaultClass \Kirby\Panel\Model
 */
class ModelTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/ModelTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

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
        $app = $this->app->clone([
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
        $app = $this->app->clone([
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

        $app = $this->app->clone([
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
        $this->assertArrayHasKey('type', $icon);
        $this->assertArrayHasKey('ratio', $icon);
        $this->assertArrayHasKey('color', $icon);
        $this->assertArrayHasKey('back', $icon);
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
        $this->assertArrayHasKey('ratio', $image);
        $this->assertArrayHasKey('back', $image);
        $this->assertArrayHasKey('cover', $image);
        $this->assertSame('3/2', $image['ratio']);
        $this->assertSame(false, $image['cover']);

        // deactivate
        $this->assertNull($panel->image(false));

        // icon
        $this->assertSame([], $panel->image('icon'));

        // invalid query
        $image = $panel->image('site.foo');
        $this->assertArrayNotHasKey('url', $image);
        $this->assertArrayNotHasKey('query', $image);

        // valid query
        $image = $panel->image('site.image');
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('cards', $image);
        $this->assertArrayHasKey('list', $image);
        $this->assertArrayNotHasKey('query', $image);

        // full options
        $image = $panel->image([
            'ratio' => '16/9',
            'query' => 'site.image',
            'cover' => true
        ]);
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('cards', $image);
        $this->assertArrayHasKey('list', $image);
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
     * @covers ::imagePlaceholder
     */
    public function testImagePlaceholder()
    {
        $this->assertIsString(Model::imagePlaceholder());
        $this->assertStringStartsWith('data:image/gif;base64,', Model::imagePlaceholder());
    }

    /**
     * @covers ::lock
     */
    public function testLock()
    {
        // content locking not supported
        $site = new ModelSiteNoLocking();
        $this->assertFalse($site->panel()->lock());

        Dir::make($this->fixtures . '/content');
        $app = $this->app->clone();
        $app->impersonate('kirby');

        // no lock or unlock
        $site = new ModelSite();
        $this->assertSame(['state' => null], $site->panel()->lock());

        // lock
        $site = new ModelSiteTestForceLocked();
        $lock = $site->panel()->lock();
        $this->assertSame('lock', $lock['state']);
        $this->assertSame('foo@bar.com', $lock['data']['email']);

        // unlock
        $site = new ModelSiteTestForceUnlocked();
        $this->assertSame(['state' => 'unlock'], $site->panel()->lock());
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

        $app = $this->app->clone();
        $app->impersonate('kirby');

        $props = $this->panel($site)->props();
        $this->assertSame('site', $props['blueprint']);
        $this->assertSame('main', $props['tabs'][0]['name']);
        $this->assertSame('main', $props['tab']['name']);
        $this->assertTrue($props['permissions']['update']);

        $app = $this->app->clone([
            'request' => [
                'query' => 'tab=foo'
            ]
        ]);
        $app->impersonate('kirby');

        $props = $this->panel($site)->props();
        $this->assertSame('foo', get('tab'));
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
