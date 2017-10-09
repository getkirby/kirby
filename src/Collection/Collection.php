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

    public function query(array $arguments): self
    {

        $result = clone $this;

        if (isset($arguments['not'])) {
            $result = $result->not(...$arguments['not']);
        }

        if (isset($arguments['filterBy'])) {
            foreach ($arguments['filterBy'] as $filter) {
                $result = $result->filterBy($filter['field'], $filter['operator'] ?? '==', $filter['value']);
            }
        }

        if (isset($arguments['offset'])) {
            $result = $result->offset($arguments['offset']);
        }

        if (isset($arguments['limit'])) {
            $result = $result->limit($arguments['limit']);
        }

        if (isset($arguments['sortBy'])) {
            if (is_array($arguments['sortBy'])) {
                $sort = explode(' ', implode(' ', $arguments['sortBy']));
            } else {
                $sort = explode(' ', $arguments['sortBy']);
            }
            $result = $result->sortBy(...$sort);
        }

        if (isset($arguments['paginate'])) {
            $result = $result->paginate($arguments['paginate']);
        }

        return $result;
    }


}
