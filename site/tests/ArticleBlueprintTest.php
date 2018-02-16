<?php

namespace Kirby\Cms;

class ArticleBlueprintTest extends BlueprintTestCase
{

    public $blueprint = 'article';

    public function testTitle()
    {
        $this->assertBlueprintTitle('Article');
    }

    public function testTabs()
    {
        $this->assertCount(1, $this->blueprintTabs());
        $this->assertBlueprintHasTab('main');
    }

    public function tabLabelProvider()
    {
        return [
            ['main', 'Main']
        ];
    }

    /**
     * @dataProvider tabLabelProvider
     */
    public function testTabLabel(string $name, string $label)
    {
        $tab = $this->blueprintTab($name);
        $this->assertEquals($label, $tab['label']);
    }

}
