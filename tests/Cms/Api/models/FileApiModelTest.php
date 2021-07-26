<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class FileApiModelTest extends ApiModelTestCase
{
    public function testNextWithTemplate()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg', 'content' => ['template' => 'test']],
                ['filename' => 'b.jpg', 'content' => ['template' => 'test']],
            ]
        ]);

        $next = $this->attr($page->file('a.jpg'), 'nextWithTemplate');
        $this->assertSame('b.jpg', $next['filename']);
    }

    public function testPrevWithTemplate()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg', 'content' => ['template' => 'test']],
                ['filename' => 'b.jpg', 'content' => ['template' => 'test']],
            ]
        ]);

        $next = $this->attr($page->file('b.jpg'), 'prevWithTemplate');
        $this->assertSame('a.jpg', $next['filename']);
    }
}
