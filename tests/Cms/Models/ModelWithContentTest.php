<?php

namespace Kirby\Cms;

class MyModel extends ModelWithContent
{
    public function blueprint()
    {
        return 'test';
    }

    protected function commit(string $action, array $arguments, \Closure $callback)
    {
        return;
    }

    public function contentFileName(): string
    {
        return 'test.txt';
    }

    public function root(): ?string
    {
        return '/tmp';
    }
}

class MyFailModel extends MyModel
{
    public function root(): ?string
    {
        return null;
    }
}

class ModelWithContentTest extends TestCase
{

    public function testContentLock()
    {
        $model = new MyModel();
        $this->assertInstanceOf('Kirby\\Cms\\ContentLock', $model->lock());
    }

    public function testContentLockWithNoDirectory()
    {
        $model = new MyFailModel();
        $this->assertNull($model->lock());
    }
}

