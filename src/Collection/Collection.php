<?php

namespace Kirby\Collection;

use Kirby\Collection\Traits\Converter;
use Kirby\Collection\Traits\Filter;
use Kirby\Collection\Traits\Finder;
use Kirby\Collection\Traits\Getter;
use Kirby\Collection\Traits\Mutator;
use Kirby\Collection\Traits\Navigator;
use Kirby\Collection\Traits\Paginator;
use Kirby\Collection\Traits\Sorter;

class Collection extends Iterator
{

    use Converter;
    use Finder;
    use Filter;
    use Getter;
    use Mutator;
    use Navigator;
    use Paginator;
    use Sorter;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->set($data);
    }

}
