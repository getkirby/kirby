<?php

namespace Kirby\Cms;

class PageTestCase extends TestCase
{
    public $page = null;

    public function page(?string $id = null)
    {
        return parent::page($id ?? $this->page);
    }

    public function assertPageTitle(string $title)
    {
        $this->assertPageField('title', $title);
    }

    public function assertPageSlug(string $slug)
    {
        $this->assertEquals($slug, $this->page()->slug());
    }

    public function assertPageTemplate(string $template)
    {
        $this->assertEquals($template, $this->page()->template());
    }

    public function assertPageField(string $key, string $value)
    {
        $this->assertEquals($value, $this->page()->content()->get($key)->value());
    }

    public function assertPageModel(string $className)
    {
        $this->assertInstanceOf($className, $this->page());
    }

    public function assertPageHasChildren($count = null)
    {
        if ($count === null) {
            $this->assertTrue($this->page()->hasChildren());
        } else {
            $this->assertCount($count, $this->page()->children());
        }
    }

    public function assertPageHasNoChildren()
    {
        $this->assertFalse($this->page()->hasChildren());
    }

    public function assertPageHasChild($slug)
    {
        $child = $this->page()->find($slug);
        $this->assertIsPage($child, $this->page()->id() . '/' . $slug);
    }

    public function assertPageHasFiles($count = null)
    {
        if ($count === null) {
            $this->assertTrue($this->page()->hasFiles());
        } else {
            $this->assertCount($count, $this->page()->files());
        }
    }

    public function assertPageHasNoFiles()
    {
        $this->assertFalse($this->page()->hasFiles());
    }

    public function assertPageHasFile($filename)
    {
        $file = $this->page()->file($filename);
        $this->assertIsFile($file, $this->page()->id() . '/' . $filename);
    }

    public function testIfPageExists()
    {
        $this->assertNotNull($this->page());
        $this->assertTrue($this->page()->exists());
    }
}
