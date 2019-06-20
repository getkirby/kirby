<?php

namespace Kirby\Cms;

class MyModel extends Model
{
    protected $id;

    public function __construct(array $props = [])
    {
        $this->setProperties($props);
        $this->setKirby($props['kirby'] ?? null);
    }

    protected function setId($id = null)
    {
        $this->id = $id;
        return $this;
    }

    public function id()
    {
        return $this->id;
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

    public function testToString()
    {
        $model = new MyModel([
            'id' => 'test'
        ]);

        $this->assertEquals('test', $model->__toString());
        $this->assertEquals('test', (string)$model);
    }

    public function testToArray()
    {
        $model = new MyModel([
            'id' => 'test'
        ]);

        $this->assertEquals(['id' => 'test'], $model->toArray());
    }
}
