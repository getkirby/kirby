<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

class FileStoreDefault extends Store
{

    const MODEL = File::class;

    public function asset()
    {
        return new Image($this->file()->filename(), $this->file()->url());
    }

    public function changeName(string $name): File
    {
        return $this->file()->clone([
            'filename' => $name . '.' . $this->file()->extension()
        ]);
    }

    public function content()
    {
        return [];
    }

    public function create(File $file, Upload $upload)
    {
        return $file;
    }

    public function delete(): bool
    {
        throw new Exception('This file cannot be deleted');
    }

    public function exists(): bool
    {
        return false;
    }

    public function file()
    {
        return $this->model;
    }

    public function id()
    {
        return $this->file()->filename();
    }

    public function replace(Upload $upload)
    {
        return $this->create($this->file(), $upload);
    }

    public function update(array $values = [], array $strings = [])
    {
        return $this->file()->clone([
            'content' => $this->file()->content()->update($strings)->toArray()
        ]);
    }

}
