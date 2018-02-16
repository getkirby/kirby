<?php

namespace Kirby\Cms;

class BlogPageTest extends PageTestCase
{

    public $page = 'blog';

    public function testTitle()
    {
        $this->assertPageTitle('Blog');
    }

    public function testSlug()
    {
        $this->assertPageSlug('blog');
    }

    public function testTemplate()
    {
        $this->assertPageTemplate('blog');
    }

    public function testModel()
    {
        $this->assertPageModel(Page::class);
    }

    public function testPerPageSettings()
    {
        $this->assertPageField('perpage', 2);
    }

    public function testChildren()
    {
        $this->assertPageHasChildren(3);

        $this->assertPageHasChild('extending-kirby');
        $this->assertPageHasChild('licensing-kirby');
        $this->assertPageHasChild('content-in-kirby');
    }

    public function testFiles()
    {
        $this->assertPageHasNoFiles();
    }

}
