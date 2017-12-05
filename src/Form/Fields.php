<?php

namespace Kirby\Form;

use Exception;
use Kirby\Collection\Collection;

class Fields extends Collection
{

    public function __construct(array $fields)
    {
        foreach ($fields as $field) {

            if ($this->has($field->name())) {
                throw new Exception(sprintf('All field names must be unique. "%s" is a duplicate', $field->name()));
            }

            $this->append($field->name(), $field);
        }
    }

    public function toHtml()
    {
        return implode($this->toArray(function ($field) {
            return $field->element();
        }));
    }

}
