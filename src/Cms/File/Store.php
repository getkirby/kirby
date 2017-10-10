<?php

namespace Kirby\Cms\File;

use Exception;

use Kirby\Cms\File;
use Kirby\Data\Data;

class Store
{

    protected $file;
    protected $db;

    public function __construct(File $file, $attributes = [])
    {
        $this->file = $file;
        $this->db   = $file->root() . '.txt';
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
        return Data::write($this->db, $data);
    }

    public function delete()
    {

        if (@unlink($this->file->root()) && @unlink($this->db())) {
            return true;
        }

        return false;
    }

}
