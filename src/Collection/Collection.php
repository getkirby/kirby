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

    public function isEven(): bool
    {
        return $this->count() % 2 === 0;
    }

    public function isOdd(): bool
    {
        return $this->count() % 2 !== 0;
    }

    public function query(array $arguments): self
    {
        $result = clone $this;

        if (isset($arguments['not']) === true) {
            $result = $result->not(...$arguments['not']);
        }

        if (isset($arguments['filterBy']) === true) {
            foreach ($arguments['filterBy'] as $filter) {
                if (isset($filter['field']) === true && isset($filter['value']) === true) {
                    $result = $result->filterBy($filter['field'], $filter['operator'] ?? '==', $filter['value']);
                }
            }
        }

        if (isset($arguments['offset']) === true) {
            $result = $result->offset($arguments['offset']);
        }

        if (isset($arguments['limit']) === true) {
            $result = $result->limit($arguments['limit']);
        }

        if (isset($arguments['sortBy']) === true) {
            if (is_array($arguments['sortBy'])) {
                $sort = explode(' ', implode(' ', $arguments['sortBy']));
            } else {
                $sort = explode(' ', $arguments['sortBy']);
            }
            $result = $result->sortBy(...$sort);
        }

        if (isset($arguments['paginate']) === true) {
            $result = $result->paginate($arguments['paginate']);
        }

        return $result;
    }

    public function __debuginfo(): array
    {
        return $this->keys();
    }
}
