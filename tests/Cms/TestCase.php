<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    public function assertIsPage($input, $id = null)
    {
        $this->assertInstanceOf(Page::class, $input);

        if (is_string($id)) {
            $this->assertEquals($id, $input->id());
        }

        if (is_a($id, Page::class)) {
            $this->assertEquals($input, $id);
        }
    }

    public function assertIsFile($input, $id = null)
    {
        $this->assertInstanceOf(File::class, $input);

        if (is_string($id)) {
            $this->assertEquals($id, $input->id());
        }

        if (is_a($id, File::class)) {
            $this->assertEquals($input, $id);
        }
    }

}
