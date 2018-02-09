<?php

namespace Kirby\Form\Assertions;

trait Type
{

    public function assertTypeProperty(string $type)
    {
        $this->assertEquals($type, $this->field()->type());
    }

}
