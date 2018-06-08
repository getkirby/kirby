<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;

class Query extends Obj
{

    public function __construct($query)
    {
        if (is_string($query) === true) {
            parse_str(ltrim($query, '?'), $query);
        }

        parent::__construct($query ?? []);
    }

    public function isEmpty(): bool
    {
        return empty((array)$this) === true;
    }

    public function isNotEmpty(): bool
    {
        return empty((array)$this) === false;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString($questionMark = false): string
    {
        $query = http_build_query($this);

        if (empty($query) === false && $questionMark === true) {
            $query = '?' . $query;
        }

        return $query;
    }

}
