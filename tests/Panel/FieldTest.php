<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Field
 */
class FieldTest extends TestCase
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
        // clear session file first
        $this->app->session()->destroy();

        Dir::remove($this->tmp);
    }

    /**
     * @covers ::email
     * @return void
     */
    public function testEmail(): void
    {
        // default
        $field = Field::email();
        $expected = [
            'label'   => 'Email',
            'type'    => 'email',
            'counter' => false
        ];

        $this->assertSame($expected, $field);

        // with custom props
        $field = Field::email([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::filePosition
     * @return void
     */
    public function testFilePosition(): void
    {
        $this->app = $this->app->clone([
            'site' => [
                'files' => [
                    ['filename' => 'a.jpg'],
                    ['filename' => 'b.jpg'],
                    ['filename' => 'c.jpg']
                ]
            ]
        ]);

        $site = $this->app->site();
        $file = $site->file('b.jpg');

        // default
        $field = Field::filePosition($file);

        $this->assertSame('Change position', $field['label']);
        $this->assertSame('select', $field['type']);
        $this->assertFalse($field['empty']);

        // check options
        $this->assertCount(5, $field['options']);

        $this->assertSame(1, $field['options'][0]['value']);
        $this->assertSame(1, $field['options'][0]['text']);

        $this->assertSame('a.jpg', $field['options'][1]['value']);
        $this->assertSame('a.jpg', $field['options'][1]['text']);
        $this->assertTrue($field['options'][1]['disabled']);

        $this->assertSame(2, $field['options'][2]['value']);
        $this->assertSame(2, $field['options'][2]['text']);

        $this->assertSame('c.jpg', $field['options'][3]['value']);
        $this->assertSame('c.jpg', $field['options'][3]['text']);
        $this->assertTrue($field['options'][3]['disabled']);

        $this->assertSame(3, $field['options'][4]['value']);
        $this->assertSame(3, $field['options'][4]['text']);

        // with custom props
        $field = Field::filePosition($file, [
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::hidden
     * @return void
     */
    public function testHidden(): void
    {
        $field = Field::hidden();
        $this->assertSame(['type' => 'hidden'], $field);
    }

    /**
     * @covers ::pagePosition
     * @return void
     */
    public function testPagePosition(): void
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'a', 'num' => 1],
                    ['slug' => 'b', 'num' => 2],
                    ['slug' => 'c', 'num' => 3]
                ]
            ]
        ]);

        $site = $this->app->site();
        $page = $site->find('b');

        // default
        $field = Field::pagePosition($page);

        $this->assertSame('Please select a position', $field['label']);
        $this->assertSame('select', $field['type']);
        $this->assertFalse($field['empty']);

        // check options
        $this->assertCount(5, $field['options']);

        $this->assertSame(1, $field['options'][0]['value']);
        $this->assertSame(1, $field['options'][0]['text']);

        $this->assertSame('a', $field['options'][1]['value']);
        $this->assertSame('a', $field['options'][1]['text']);
        $this->assertTrue($field['options'][1]['disabled']);

        $this->assertSame(2, $field['options'][2]['value']);
        $this->assertSame(2, $field['options'][2]['text']);

        $this->assertSame('c', $field['options'][3]['value']);
        $this->assertSame('c', $field['options'][3]['text']);
        $this->assertTrue($field['options'][3]['disabled']);

        $this->assertSame(3, $field['options'][4]['value']);
        $this->assertSame(3, $field['options'][4]['text']);

        // with custom props
        $field = Field::pagePosition($page, [
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::pagePosition
     * @return void
     */
    public function testPagePositionWithNotEnoughOptions(): void
    {
        $this->app = $this->app->clone([
            'site' => [
                'children' => [
                    ['slug' => 'a', 'num' => 1],
                ]
            ]
        ]);

        $site  = $this->app->site();
        $page  = $site->find('a');
        $field = Field::pagePosition($page);

        $this->assertSame('hidden', $field['type']);
    }

    /**
     * @covers ::password
     * @return void
     */
    public function testPassword(): void
    {
        // default
        $field = Field::password();
        $expected = [
            'label'   => 'Password',
            'type'    => 'password',
        ];

        $this->assertSame($expected, $field);

        // with custom props
        $field = Field::password([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::role
     * @return void
     */
    public function testRole(): void
    {
        $field = Field::role();
        $expected = [
            'label'   => 'Role',
            'type'    => 'hidden',
            'options' => []
        ];

        $this->assertSame($expected, $field);

        // without authenticated user
        $this->app = $this->app->clone([
            'blueprints' => [
                'users/admin'  => [
                    'name'        => 'admin',
                    'title'       => 'Admin',
                    'description' => 'Admin description'
                ],
                'users/editor' => [
                    'name'        => 'editor',
                    'title'       => 'Editor',
                    'description' => 'Editor description'
                ],
                'users/client' => [
                    'name'  => 'client',
                    'title' => 'Client'
                ]
            ]
        ]);

        $field = Field::role();
        $expected = [
            'label'   => 'Role',
            'type'    => 'radio',
            'options' => [
                [
                    'text' => 'Client',
                    'info' => 'No description',
                    'value' => 'client'
                ],
                [
                    'text' => 'Editor',
                    'info' => 'Editor description',
                    'value' => 'editor'
                ],
            ]
        ];

        $this->assertSame($expected, $field);

        // with authenticated admin
        $this->app->impersonate('kirby');

        $field = Field::role();
        $expected = [
            'label'   => 'Role',
            'type'    => 'radio',
            'options' => [
                [
                    'text' => 'Admin',
                    'info' => 'Admin description',
                    'value' => 'admin'
                ],
                [
                    'text' => 'Client',
                    'info' => 'No description',
                    'value' => 'client'
                ],
                [
                    'text' => 'Editor',
                    'info' => 'Editor description',
                    'value' => 'editor'
                ],
            ]
        ];

        $this->assertSame($expected, $field);
    }

    /**
     * @covers ::slug
     */
    public function testSlug(): void
    {
        // default
        $field = Field::slug();
        $expected = [
            'label' => 'URL appendix',
            'type'  => 'slug',
        ];

        $this->assertSame($expected, $field);

        // with custom props
        $field = Field::slug([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::title
     * @return void
     */
    public function testTitle(): void
    {
        // default
        $field = Field::title();
        $expected = [
            'label' => 'Title',
            'type'  => 'text',
            'icon'  => 'title'
        ];

        $this->assertSame($expected, $field);

        // with custom props
        $field = Field::title([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::template
     * @return void
     */
    public function testTemplate(): void
    {
        // default = no templates available
        $field    = Field::template();
        $expected = [
            'label'    => 'Template',
            'type'     => 'select',
            'empty'    => false,
            'options'  => [],
            'icon'     => 'template',
            'disabled' => true
        ];

        $this->assertSame($expected, $field);

        // select option format
        $options = [
            [
                'text'  => 'A',
                'value' => 'a'
            ],
            [
                'text'  => 'B',
                'value' => 'b'
            ]
        ];

        $field = Field::template($options);
        $this->assertSame($options, $field['options']);
        $this->assertFalse($field['disabled']);

        // blueprint format
        $blueprints = [
            [
                'title' => 'A',
                'name'  => 'a'
            ],
            [
                'title' => 'B',
                'name'  => 'b'
            ]
        ];

        $expected = [
            [
                'text'  => 'A',
                'value' => 'a'
            ],
            [
                'text'  => 'B',
                'value' => 'b'
            ]
        ];

        $field = Field::template($blueprints);
        $this->assertSame($expected, $field['options']);
        $this->assertFalse($field['disabled']);

        // with custom props
        $field = Field::template([], ['required' => true]);
        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::translation
     * @return void
     */
    public function testTranslation(): void
    {
        // default
        $field = Field::translation();

        $this->assertSame('Language', $field['label']);
        $this->assertSame('select', $field['type']);
        $this->assertSame('globe', $field['icon']);
        $this->assertFalse($field['empty']);
        $this->assertCount($this->app->translations()->count(), $field['options']);

        // with custom props
        $field = Field::translation([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }

    /**
     * @covers ::username
     * @return void
     */
    public function testUsername(): void
    {
        // default
        $field = Field::username();
        $expected = [
            'icon'  => 'user',
            'label' => 'Name',
            'type'  => 'text',
        ];

        $this->assertSame($expected, $field);

        // with custom props
        $field = Field::username([
            'required' => true
        ]);

        $this->assertTrue($field['required']);
    }
}
