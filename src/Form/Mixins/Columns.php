<?php

namespace Kirby\Form\Mixins;

trait Columns
{

    protected $columns;

    protected function defaultColumns()
    {
        return 2;
    }

    public function columns(): int
    {
        return $this->columns;
    }

    protected function setColumns(int $columns = 2)
    {
        $this->columns = $columns;
        return $this;
    }

}
