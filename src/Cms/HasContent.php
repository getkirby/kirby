<?php

namespace Kirby\Cms;

trait HasContent
{

    /**
     * Modified getter to also return fields
     * from the object's content
     *
     * @param string $method
     * @return mixed
     */
    public function get(string $method)
    {
        if ($this->props->has($method, true)) {
            return $this->props->get($method);
        }

        if ($this->hasPlugin($method)) {
            return $this->plugin($method);
        }

        return $this->content()->get($method);
    }

    /**
     * Returns a formatted date field from the content
     *
     * @param string $format
     * @param string $field
     * @return Field
     */
    public function date(string $format = null, $field = 'date')
    {
        return $this->content()->get($field, [$format]);
    }

}
