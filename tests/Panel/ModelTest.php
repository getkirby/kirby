<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Site as ModelSite;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
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

    public function view(): array
    {
        return [];
    }
}

class ModelSiteWithImageMethod extends ModelSite
{
    public function panelBack()
    {
        return 'blue';
    }

    public function cover()
    {
        return new Asset('tmp/test.svg');
    }
}

/**
 * @coversDefaultClass \Kirby\Panel\Model
 */
class ModelTest extends TestCase
{
    protected $app;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
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

    public function testDropdown()
    {
        $model  = new CustomPanelModel(new ModelSite());
        $option = $model->dropdownOption();
        $expected = [
            'icon' => 'page',
            'link' => '/panel/custom',
            'text' => null
        ];

        $this->assertSame($expected, $option);
    }

    /**
     * @covers ::image
     * @covers ::imageDefaults
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
        $this->assertArrayHasKey('back', $image);
        $this->assertArrayHasKey('cover', $image);
        $this->assertArrayHasKey('icon', $image);
        $this->assertArrayHasKey('ratio', $image);
        $this->assertSame(false, $image['cover']);
        $this->assertSame('page', $image['icon']);
        $this->assertSame('3/2', $image['ratio']);

        // deactivate
        $this->assertNull($panel->image(false));

        // icon
        $image = $panel->image('icon');
        $this->assertSame('page', $image['icon']);
        $this->assertArrayNotHasKey('src', $image);
        $this->assertArrayNotHasKey('url', $image);

        // invalid query
        $image = $panel->image('site.foo');
        $this->assertArrayNotHasKey('url', $image);
        $this->assertArrayNotHasKey('query', $image);

        // valid query
        $image = $panel->image('site.image');
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('src', $image);
        $this->assertArrayHasKey('srcset', $image);
        $this->assertArrayNotHasKey('query', $image);
        $this->assertStringContainsString('test-38x.jpg 38w', $image['srcset']);
        $this->assertStringContainsString('test-76x.jpg 76w', $image['srcset']);

        // cards
        $image = $panel->image('site.image', 'cards');
        $this->assertStringContainsString('test-352x.jpg 352w', $image['srcset']);
        $this->assertStringContainsString('test-864x.jpg 864w', $image['srcset']);
        $this->assertStringContainsString('test-1408x.jpg 1408w', $image['srcset']);

        // cards with cover option should still return the full srcset
        // cropping is done in css
        $image = $panel->image([
            'query'  => 'site.image',
            'cover'  => true
        ], 'cards');

        $this->assertStringContainsString('test-352x.jpg 352w', $image['srcset']);
        $this->assertStringContainsString('test-864x.jpg 864w', $image['srcset']);
        $this->assertStringContainsString('test-1408x.jpg 1408w', $image['srcset']);

        // cardlets
        $image = $panel->image('site.image', 'cardlets');
        $this->assertStringContainsString('test-96x.jpg 96w', $image['srcset']);
        $this->assertStringContainsString('test-192x.jpg 192w', $image['srcset']);

        // full options
        $image = $panel->image([
            'cover' => true,
            'icon'  => $icon = 'heart',
            'query' => 'site.image',
            'ratio' => $ratio = '16/9'
        ]);
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('src', $image);
        $this->assertArrayHasKey('srcset', $image);
        $this->assertSame($icon, $image['icon']);
        $this->assertSame($ratio, $image['ratio']);
        $this->assertStringContainsString('test-38x38-crop.jpg 1x', $image['srcset']);
        $this->assertStringContainsString('test-76x76-crop.jpg 2x', $image['srcset']);
    }

    /**
     * @covers ::image
     */
    public function testImageWithNonResizableAsset()
    {
        $site  = new ModelSiteWithImageMethod([]);
        $panel = new CustomPanelModel($site);
        $image = $panel->image('site.cover');
        $this->assertSame('//tmp/test.svg', $image['url']);
        $this->assertSame('//tmp/test.svg', $image['src']);
    }

    /**
     * @covers ::image
     */
    public function testImageWithBlueprint()
    {
        $app  = $this->app->clone([
            'blueprints' => [
                'pages/test' => [
                    'icon' => 'heart',
                    'image'  => [
                        'back' => 'red',
                        'ratio' => '1/2'
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'template' => 'test'
                    ]
                ]
            ]
        ]);

        $panel = $app->page('test')->panel();
        $image = $panel->image(['ratio' => '1/1', 'color' => 'yellow']);
        $this->assertSame('red', $image['back']);
        $this->assertSame('yellow', $image['color']);
        $this->assertSame('heart', $image['icon']);
        $this->assertSame('1/2', $image['ratio']);
        $this->assertArrayNotHasKey('query', $image);
        $this->assertArrayNotHasKey('url', $image);
    }

    /**
     * @covers ::image
     */
    public function testImageWithQuery()
    {
        $site  = new ModelSiteWithImageMethod();
        $panel = new CustomPanelModel($site);
        $image = $panel->image([ 'back' => '{{ site.panelBack }}']);
        $this->assertSame('blue', $image['back']);
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

        Dir::make($this->tmp . '/content');
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
     * @covers ::toLink
     */
    public function testToLink()
    {
        $panel = $this->panel([
            'content' => [
                'title'  => $title = 'Kirby Kirby Kirby',
                'author' => $author = 'Bastian Allgeier'
            ]
        ]);

        $toLink = $panel->toLink();
        $this->assertSame('/custom', $toLink['link']);
        $this->assertSame($title, $toLink['tooltip']);

        $toLink = $panel->toLink('author');
        $this->assertSame('/custom', $toLink['link']);
        $this->assertSame($author, $toLink['tooltip']);
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
