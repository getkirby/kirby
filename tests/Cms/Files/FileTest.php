<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Toolkit\F;

class FileTestModel extends File
{
}

class FileTest extends TestCase
{
    protected function defaults(): array
    {
        return [
            'filename' => 'cover.jpg',
            'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
        ];
    }

    protected function file(array $props = [])
    {
        return new File(array_merge($this->defaults(), $props));
    }

    public function testAsset()
    {
        $file = $this->file();

        $this->assertInstanceOf(Image::class, $file->asset());
        $this->assertEquals(null, $file->asset()->url());
    }

    public function testContent()
    {
        $file = $this->file([
            'content' => [
                'test' => 'Test'
            ]
        ]);

        $this->assertEquals('Test', $file->content()->get('test')->value());
    }

    public function testDefaultContent()
    {
        $file = $this->file();

        $this->assertInstanceOf(Content::class, $file->content());
    }

    public function testDragText()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $page->file('test.pdf');

        $this->assertEquals('(file: test.pdf)', $file->dragText());
        $this->assertEquals('[test.pdf](test.pdf)', $file->dragText('markdown'));
    }

    public function testDragTextForImages()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.jpg'
                ]
            ]
        ]);

        $file = $page->file('test.jpg');

        $this->assertEquals('(image: test.jpg)', $file->dragText());
        $this->assertEquals('![](test.jpg)', $file->dragText('markdown'));
    }

    public function testFilename()
    {
        $this->assertEquals($this->defaults()['filename'], $this->file()->filename());
    }

    public function testPage()
    {
        $file = $this->file([
            'parent' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals($page, $file->page());
    }

    public function testParentId()
    {
        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('test', $file->parentId());

        $file = new File([
            'filename' => 'test.jpg',
        ]);

        $this->assertNull($file->parentId());
    }

    public function testDefaultPage()
    {
        return $this->assertNull($this->file()->page());
    }

    public function testUrl()
    {
        $this->assertEquals($this->defaults()['url'], $this->file()->url());
    }

    public function testToString()
    {
        $file = new File(['filename' => 'super.jpg']);
        $this->assertEquals('super.jpg', $file->toString('{{ file.filename }}'));
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
                'content' => $index
            ]
        ]);

        // create a file
        F::write($file = $index . '/test.js', 'test');

        $modified = filemtime($file);
        $file     = $app->file('test.js');

        $this->assertEquals($modified, $file->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $file->modified($format));

        // custom date handler
        $format = '%d.%m.%Y';
        $this->assertEquals(strftime($format, $modified), $file->modified($format, 'strftime'));

        Dir::remove(dirname($index));
    }

    public function testModifiedContent()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
                'content' => $index
            ]
        ]);

        // create a file
        F::write($file = $index . '/test.js', 'test');

        // write the content file a bit later
        usleep(1000000);

        F::write($content = $index . '/test.js.txt', 'test');

        $modifiedFile    = F::modified($file);
        $modifiedContent = F::modified($content);

        $file = $app->file('test.js');

        $this->assertNotEquals($modifiedFile, $file->modified());
        $this->assertEquals($modifiedContent, $file->modified());

        Dir::remove(dirname($index));
    }

    public function testPanelIconDefault()
    {
        $file = new File([
            'filename' => 'something.jpg'
        ]);

        $icon     = $file->panelIcon();
        $expected = [
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => null
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelIconWithRatio()
    {
        $file = new File([
            'filename' => 'something.jpg'
        ]);

        $icon     = $file->panelIcon(['ratio' => '3/2']);
        $expected = [
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => '3/2'
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'mother',
                        'children' => [
                            [
                                'slug' => 'child',
                                'files' => [
                                    ['filename' => 'page-file.jpg'],
                                ]
                            ]
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'site-file.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'id'    => 'test',
                    'files' => [
                        ['filename' => 'user-file.jpg']
                    ]
                ]
            ]
        ]);

        // site file
        $file = $app->file('site-file.jpg');

        $this->assertEquals('https://getkirby.com/panel/site/files/site-file.jpg', $file->panelUrl());
        $this->assertEquals('/site/files/site-file.jpg', $file->panelUrl(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');

        $this->assertEquals('https://getkirby.com/panel/pages/mother+child/files/page-file.jpg', $file->panelUrl());
        $this->assertEquals('/pages/mother+child/files/page-file.jpg', $file->panelUrl(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');

        $this->assertEquals('https://getkirby.com/panel/users/test/files/user-file.jpg', $file->panelUrl());
        $this->assertEquals('/users/test/files/user-file.jpg', $file->panelUrl(true));
    }

    public function testApiUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'mother',
                        'children' => [
                            [
                                'slug' => 'child',
                                'files' => [
                                    ['filename' => 'page-file.jpg'],
                                ]
                            ]
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'site-file.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'id'    => 'test',
                    'files' => [
                        ['filename' => 'user-file.jpg']
                    ]
                ]
            ]
        ]);

        // site file
        $file = $app->file('site-file.jpg');

        $this->assertEquals('https://getkirby.com/api/site/files/site-file.jpg', $file->apiUrl());
        $this->assertEquals('site/files/site-file.jpg', $file->apiUrl(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');

        $this->assertEquals('https://getkirby.com/api/pages/mother+child/files/page-file.jpg', $file->apiUrl());
        $this->assertEquals('pages/mother+child/files/page-file.jpg', $file->apiUrl(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');

        $this->assertEquals('https://getkirby.com/api/users/test/files/user-file.jpg', $file->apiUrl());
        $this->assertEquals('users/test/files/user-file.jpg', $file->apiUrl(true));
    }
}
