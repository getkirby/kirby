<?php

namespace Kirby\Users;

use Kirby\Fields\Fields;
use Kirby\Fields\Field;
use Kirby\Object\Attributes;
use Kirby\Users\User\Auth;
use Kirby\Users\User\Avatar;
use Kirby\Users\User\Store;

class User
{

    protected $attributes = [];
    protected $auth;
    protected $data;
    protected $store;
    protected $avatar;

    public function __construct(array $attributes)
    {

        $this->attributes = Attributes::create($attributes, [
            'id' => [
                'type'     => 'string',
                'required' => true
            ],
            'data' => [
                'type' => 'array'
            ]
        ]);

        // setup the store
        if (is_a($attributes['store'] ?? null, Store::class)) {
            $this->store = $attributes['store'];
        }

        // setup authentication
        if (is_a($attributes['auth'] ?? null, Auth::class)) {
            $this->auth = $attributes['auth'];
            $this->auth->user($this);
        }

        // setup the avatar
        if (is_a($attributes['avatar'] ?? null, Avatar::class)) {
            $this->avatar = $attributes['avatar'];
        }

    }

    public function id(): string
    {
        return $this->attributes['id'];
    }

    public function avatar()
    {
        return $this->avatar;
    }

    public function data(): Fields
    {

        if (is_a($this->data, Fields::class)) {
            return $this->data;
        }

        if (is_array($this->data)) {
            // convert data arrays to field objects
            $data = $this->data;
        } elseif (isset($this->attributes['data']) && is_array($this->attributes['data'])) {
            // take content from the passed attributes first
            $data = $this->attributes['data'];
        } else {
            // read data from the store
            $data = $this->store->read();
        }

        return $this->data = new Fields($data, function ($key, $value) {
            return new Field($key, $value);
        });

    }

    public function role()
    {
        return (string)$this->data()->get('role') ?? 'visitor';
    }

    public function exists(): bool
    {
        return $this->store->exists();
    }

    public function save(): self
    {
        $this->store->save($this->data()->data());
        return $this;
    }

    public function update(array $data): self
    {
        $data = array_merge($this->data()->data(), $data);

        $this->store->write($data);
        $this->data = $data;
        return $this;
    }

    public function delete(): bool
    {
        return $this->store->delete();
    }

    public function __call($method, $arguments)
    {

        if ($this->auth !== null && method_exists($this->auth, $method)) {
            return $this->auth->$method(...$arguments);
        }

        if (isset($this->attributes[$method])) {
            return $this->attributes[$method];
        }

        return $this->data()->get($method, $arguments);

    }

}
