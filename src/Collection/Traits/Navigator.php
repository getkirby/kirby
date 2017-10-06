<?php

namespace Kirby\Collection\Traits;

trait Navigator
{

    /**
     * Returns the first element from the array
     *
     * @return mixed
     */
    public function first()
    {
        $array = $this->data;
        return array_shift($array);
    }

    /**
     * Returns the last element from the array
     *
     * @return mixed
     */
    public function last()
    {
        $array = $this->data;
        return array_pop($array);
    }

    /**
     * Returns the nth element from the array
     *
     * @param integer $n
     * @return mixed
     */
    public function nth(int $n)
    {
        return array_values($this->data)[$n] ?? null;
    }

}
