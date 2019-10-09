<?php

namespace Kirby\Cms;

class ExtendedModelWithContent extends ModelWithContent
{
    public function blueprint()
    {
        return 'test';
    }

    protected function commit(string $action, array $arguments, \Closure $callback)
    {
        // nothing to commit in the test
    }

    public function contentFileName(): string
    {
        return 'test.txt';
    }

    public function permissions()
    {
        return null;
    }

    public function root(): ?string
    {
        return '/tmp';
    }
}

class BrokenModelWithContent extends ExtendedModelWithContent
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
        $model = new ExtendedModelWithContent();
        $this->assertInstanceOf('Kirby\\Cms\\ContentLock', $model->lock());
    }

    public function testContentLockWithNoDirectory()
    {
        $model = new BrokenModelWithContent();
        $this->assertNull($model->lock());
    }
}
