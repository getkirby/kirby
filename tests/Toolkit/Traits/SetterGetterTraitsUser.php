<?php

namespace Kirby\Toolkit\Traits;

class SetterGetterTraitsUser
{
    use SetterGetter;

    protected $_store = [];

    public function set($key, $value)
    {
        $this->_store[$key] = $value;
    }

    public function get($key)
    {
        return $this->_store[$key];
    }
}
