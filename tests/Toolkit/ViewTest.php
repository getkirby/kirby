<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures/view';

    protected function _view(array $data = [])
    {
        return new View(static::FIXTURES . '/view.php', $data);
    }

    public function testFile()
    {
        $view = $this->_view();
        $this->assertEquals(static::FIXTURES . '/view.php', $view->file());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The view does not exist: invalid-file.php
     */
    public function testWithMissingFile()
    {
        $view = new View('invalid-file.php');
        $view->render();
    }

    public function testData()
    {
        $view = $this->_view();
        $this->assertEquals([], $view->data());

        $view = $this->_view(['test']);
        $this->assertEquals(['test'], $view->data());
    }

    public function testToString()
    {
        $view = $this->_view(['name' => 'Tester']);
        $this->assertEquals('Hello Tester', $view->toString());
        $this->assertEquals('Hello Tester', $view->__toString());
        $this->assertEquals('Hello Tester', (string)$view);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage View exception
     */
    public function testWithException()
    {
        $view = new View(static::FIXTURES . '/view-with-exception.php');
        $view->render();
    }
}
