<?php

namespace Kirby\Cms\File;

use Exception;

use Kirby\Cms\File;
use Kirby\Data\Data;
use Kirby\FileSystem\File as Asset;

class Store
{

    protected $root;
    protected $db;

    public function __construct($root, $attributes = [])
    {
        $this->root = $root;
        $this->db   = $root . '.txt';
    }

    public function asset()
    {
        return new Asset($this->root);
    }

    protected function db(): string
    {
        return $this->db;
    }

    public function read(): array
    {
        try {
            return Data::read($this->db());
        } catch (Exception $e) {
            return [];
        }
    }

    public function write(array $data = []): bool
    {
        return Data::write($this->db(), $data);
    }

    public function rename($name): Asset
    {

        $asset = new Asset($this->root);

        if ($asset->exists() === false) {
            return $this->asset();
        }

        $asset->rename($name);
        $this->root = $asset->root();

        $db = new Asset($this->db);

        if ($db->exists()) {
            $db->move($asset->root() . '.txt');
            $this->db = $db->root();
        }

        return $asset;

    }

    public function delete()
    {
        $asset = new Asset($this->root);
        $asset->delete();

        $db = new Asset($this->db());
        $db->delete();

        return true;
    }

}
