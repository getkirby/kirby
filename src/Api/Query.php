<?php

namespace Kirby\Api;

use Exception;
use GraphQL\Type\Definition\ObjectType;
use Kirby\Toolkit\DI\Dependencies;

class Query extends ObjectType
{

    protected $dependencies;

    public function __construct($queries, $data)
    {
        $this->dependencies = new Dependencies($data);

        foreach ($data as $key => $value) {
            $this->dependencies->set($key, $value);
        }

        foreach ($queries as $key => $query) {
            $queries[$key] = $this->query($query);
        }

        parent::__construct([
            'name'   => 'Query',
            'fields' => $queries,
        ]);
    }

    protected function query(callable $query)
    {
        return $this->dependencies->call($query);
    }

}
