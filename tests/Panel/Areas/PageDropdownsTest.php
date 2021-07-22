<?php

namespace Kirby\Panel\Areas;

class PageDropdownsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testPageDropdown(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        $this->login();

        $options = $this->dropdown('pages/test')['options'];

        $title = $options[0];
        $this->assertSame([
            'url'   => '/pages/test/changeTitle',
            'query' => [
                'select' => 'title'
            ]
        ], $title['dialog']);
        $this->assertSame('Rename', $title['text']);

        $duplicate = $options[1];
        $this->assertSame('/pages/test/duplicate', $duplicate['dialog']);
        $this->assertSame('Duplicate', $duplicate['text']);

        $this->assertSame('-', $options[2]);

        $slug = $options[3];
        $this->assertSame([
            'url'   => '/pages/test/changeTitle',
            'query' => [
                'select' => 'slug'
            ]
        ], $slug['dialog']);
        $this->assertSame('Change URL', $slug['text']);

        $status = $options[4];
        $this->assertSame('/pages/test/changeStatus', $status['dialog']);
        $this->assertSame('Change status', $status['text']);

        $template = $options[5];
        $this->assertSame('/pages/test/changeTemplate', $template['dialog']);
        $this->assertSame('Change template', $template['text']);

        $this->assertSame('-', $options[6]);

        $delete = $options[7];
        $this->assertSame('/pages/test/delete', $delete['dialog']);
        $this->assertSame('Delete', $delete['text']);
    }
}
