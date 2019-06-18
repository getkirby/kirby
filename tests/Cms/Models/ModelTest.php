<?php

namespace Kirby\Cms;

class MyModel extends Model
{
    public function __construct(array $props = [])
    {
        $this->setProperties($props);
    }
}

class ModelTest extends TestCase
{
    public function testModel()
    {
        $model = new MyModel();
        $this->assertInstanceOf(Model::class, $model);
        $this->assertInstanceOf(App::class, $model->kirby());
        $this->assertInstanceOf(Site::class, $model->site());
    }

    public function testKirby()
    {
        $kirby = new App();
        $model = new MyModel([
            'kirby' => $kirby
        ]);
        $this->assertEquals($kirby, $model->kirby());
    }

    public function testSite()
    {
        $site = new Site();
        $model = new MyModel([
            'site' => $site
        ]);
        $this->assertEquals($site, $model->site());
    }
}
