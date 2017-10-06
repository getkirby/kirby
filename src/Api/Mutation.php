<?php

namespace Kirby\Api;

use Exception;
use GraphQL\Type\Definition\ObjectType;
use Kirby\Toolkit\DI\Dependencies;

class Mutation extends ObjectType
{

    protected $dependencies;

    public function __construct($mutations, $data)
    {
        $this->dependencies = new Dependencies($data);

        foreach ($data as $key => $value) {
            $this->dependencies->set($key, $value);
        }

        foreach ($mutations as $key => $mutation) {
            $mutations[$key] = $this->mutation($mutation);
        }

        parent::__construct([
            'name'   => 'Mutation',
            'fields' => $mutations,
        ]);
    }

    protected function mutation(callable $mutation)
    {
        return $this->dependencies->call($mutation);
    }

}
