<?php

namespace Kirby\Users\User;

use Exception;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;
use Kirby\Object\Attributes;
use Kirby\Users\User;

class Store
{

    protected $attributes;
    protected $root;

    public function __construct(array $attributes)
    {
        $attributes = Attributes::create($attributes, [
            'root' => [
                'type'     => 'string',
                'required' => true
            ]
        ]);

        $this->root = $attributes['root'];
    }

    protected function db()
    {
        return $this->root . '/user.txt';
    }

    public function exists(): bool
    {
        return is_file($this->db());
    }

    public function validate(array $data = []): bool
    {
        if (empty($data['password']) === true) {
            throw new Exception('The password is required');
        }

        if (empty($data['email']) === true) {
            throw new Exception('The email is required');
        }

        return true;
    }

    public function create()
    {
        $folder = new Folder($this->root);
        $folder->make();

        touch($this->db());
    }

    public function save(array $data = [])
    {
        if ($this->exists() === true) {
            return $this->write($data);
        }

        $this->create();
        return $this->write($data);
    }

    public function read(): array
    {
        try {
            return Data::read($this->db());
        } catch (Exception $e) {
            return [];
        }
    }

    public function write($data = [])
    {

        // make sure all required fields exist
        $this->validate($data);

        // always hash passwords
        if (isset($data['password'])) {
            $info = password_get_info($data['password']);

            if ($info['algo'] === 0) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }

        return Data::write($this->db(), $data);
    }

    public function delete()
    {
        $folder = new Folder($this->root);
        return $folder->delete();
    }

}
