<?php

namespace Kirby\Cms;

class SiteRulesTest extends TestCase
{

    public function testCreateChild()
    {
        $child    = new Page(['slug' => 'projects']);
        $children = new Children([$child]);
        $site     = new Site([
            'children' => $children
        ]);
        $new      = new Page(['slug' => 'team']);
        $this->assertTrue(SiteRules::createChild($site, $new));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The URL appendix "projects" exists
     */
    public function testCreateChildDuplicate()
    {
        $child    = new Page(['slug' => 'projects']);
        $children = new Children([$child]);
        $site     = new Site([
            'children' => $children
        ]);
        $new      = new Page(['slug' => 'projects']);
        SiteRules::createChild($site, $new);
    }

    public function testCreateFile()
    {
        $site = new Site([]);
        $file = new File([
            'filename' => 'cover.jpg',
            'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
        ]);
        $this->assertTrue(SiteRules::createFile($site, $file, 'https://getkirby.com/projects/project-a/cover.jpg'));
    }

    public function testUpdate()
    {
        $site = new Site([]);
        $this->assertTrue(SiteRules::update($site, [
            'copyright' => '2018'
        ]));
    }

}
