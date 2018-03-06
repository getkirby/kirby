<?php

namespace Kirby\Cms;

use Kirby\Form\Field;
use Kirby\Form\TextField;
use Kirby\Image\Image;

class DummyField extends TextField {}
class DummyPage  extends Page {}

class AppRegistryTest extends TestCase
{

    public function testBlueprint()
    {
        $kirby = new App([
            'set' => [
                'blueprint' => [
                    'pages/test' => $file = 'test.yml'
                ]
            ]
        ]);

        $this->assertEquals($file, $kirby->get('blueprint', 'pages/test'));
    }

    public function testCollection()
    {
        $pages = new Pages([]);
        $kirby = new App([
            'set' => [
                'collection' => [
                    'test' => function () use ($pages) {
                        return $pages;
                    }
                ],
            ]
        ]);

        $this->assertEquals($pages, $kirby->collection('test'));
    }

    public function testController()
    {
        $kirby = new App([
            'set' => [
                'controller' => [
                    'test' => function () {
                        return ['foo' => 'bar'];
                    }
                ]
            ]
        ]);

        $this->assertEquals(['foo' => 'bar'], $kirby->controller('test'));
    }

    public function testContentFieldMethod()
    {
        $kirby = new App([
            'set' => [
                'fieldMethod' => [
                    'test' => function () {
                        return 'test';
                    }
                ]
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->customField()->test());
    }

    public function testField()
    {
        $app = new App([
            'set' => [
                'field' => [
                    'dummy' => DummyField::class
                ]
            ]
        ]);

        $field = Field::factory([
            'type' => 'dummy',
            'name' => 'dummy'
        ]);

        $this->assertInstanceOf(DummyField::class, $field);
    }

    public function testHook()
    {
        $phpUnit  = $this;

        $kirby = new App([
            'set' => [
                'hook' => [
                    'testHook' => function ($message) use ($phpUnit, &$executed) {
                        $phpUnit->assertEquals('test', $message);
                    }
                ]
            ]
        ]);

        $kirby->hooks()->trigger('testHook', 'test');
    }

    public function testHooks()
    {
        $phpUnit  = $this;
        $executed = 0;

        $kirby = new App([
            'set' => [
                'hook' => [
                    'testHook' => [
                        function ($message) use ($phpUnit, &$executed) {
                            $phpUnit->assertEquals('test', $message);
                            $executed++;
                        },
                        function ($message) use ($phpUnit, &$executed) {
                            $phpUnit->assertEquals('test', $message);
                            $executed++;
                        }
                    ]
                ]
            ]
        ]);

        $kirby->hooks()->trigger('testHook', 'test');
        $this->assertEquals(2, $executed);

    }

    public function testPageModel()
    {
        $kirby = new App([
            'set' => [
                'pageModel' => [
                    'dummy' => DummyPage::class
                ]
            ]
        ]);

        $page = Page::factory([
            'slug'     => 'test',
            'template' => 'dummy'
        ]);

        $this->assertInstanceOf(DummyPage::class, $page);
    }

    public function testOption()
    {
        $kirby = new App([
            'set' => [
                'option' => [
                    'testOption' => 'testValue'
                ]
            ]
        ]);

        $this->assertEquals('testValue', $kirby->option('testOption'));
    }

    public function testRoute()
    {
        $kirby = new App([
            'set' => [
                'route' => [
                    [
                        'pattern' => 'test',
                        'action'  => function () {
                            return 'test';
                        }
                    ]
                ]
            ]
        ]);

        $this->assertEquals('test', $kirby->router()->call('test'));

    }

    public function testSnippet()
    {
        $kirby = new App([
            'set' => [
                'snippet' => [
                    'header' => $file = 'header.php'
                ]
            ]
        ]);

        $this->assertEquals($file, $kirby->get('snippet', 'header'));
    }

    public function testTemplate()
    {
        $kirby = new App([
            'set' => [
                'template' => [
                    'project' => $file = 'project.php'
                ]
            ]
        ]);

        $this->assertEquals($file, $kirby->get('template', 'project'));
    }

}
