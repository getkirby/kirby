<?php

namespace Kirby\Cms;

class MyHasModel {

    use HasModel;

    public function __construct(Model $model = null)
    {
        $this->setModel($model);
    }

}

class HasModelTest extends TestCase
{

    public function testTrait()
    {
        $this->assertTrue(method_exists(MyHasModel::class, 'model'));
        $this->assertTrue(method_exists(MyHasModel::class, 'setModel'));
    }

    public function testWithModel()
    {
        $model = new Page(['slug' => 'test']);
        $obj = new MyHasModel($model);
        $this->assertEquals($model, $obj->model());
    }

    public function testWithoutModel()
    {
        $obj = new MyHasModel();
        $this->assertNull($obj->model());
    }

}
