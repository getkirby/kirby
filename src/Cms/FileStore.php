<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Image\Image;
use Kirby\Util\F;

class FileStore extends FileStoreDefault
{

    protected $root;

    public function asset()
    {
        return new Image($this->root(), $this->file()->url());
    }

    public function changeName(string $name): File
    {
        $oldFile  = $this->file();
        $oldRoot  = $this->root();
        $oldStore = $this->storeFile();

        // create a file object clone with the new name
        $newRoot  = dirname($oldRoot) . '/' . $newFilename = $name . '.' . $oldFile->extension();
        $newStore = $newRoot . '.' . $this->extension();
        $newFile  = $this->file()->clone([
            'filename' => $newFilename,
        ]);

        if ($oldFile->exists() === false) {
            return $newFile;
        }

        if ($newFile->exists() === true) {
            throw new Exception('The new file exists and cannot be overwritten');
        }

        // remove all public versions
        $this->media()->delete($oldFile->parent(), $oldFile);

        // rename the main file
        F::move($oldRoot, $newRoot);

        // rename the store file
        F::move($oldStore, $newStore);

        // create a new public version
        $this->media()->create($newFile->parent(), $newFile);

        return $newFile;
    }

    public function content(): array
    {
        try {
            return Data::read($this->storeFile());
        } catch (Exception $e) {
            return [];
        }
    }

    public function create(Upload $upload)
    {
        $file = $this->file();

        // delete all public versions
        $this->media()->delete($file->parent(), $file);

        // overwrite the original
        if (F::copy($upload->root(), $this->root()) !== true) {
            throw new Exception('The file could not be created');
        }

        if ($file->template() !== null) {
            $this->update($data = ['template' => $file->template()], $data);
        }

        // create a new public file
        $this->media()->create($file->parent(), $file);

        // return a fresh clone
        return $file->clone();
    }

    public function delete(): bool
    {
        // delete all public versions
        $this->media()->delete($this->file()->parent(), $this->file());

        F::remove($this->storeFile());
        F::remove($this->root());

        return true;
    }

    public function exists(): bool
    {
        return is_file(realpath($this->root())) === true;
    }

    public function extension(): string
    {
        return 'txt';
    }

    public function replace(Upload $upload)
    {
        return $this->create($upload);
    }

    public function root(): string
    {
        if (is_string($this->root) === true) {
            return $this->root;
        }

        $file   = $this->model();
        $parent = $file->parent();

        if (is_a($parent, Page::class) === true) {
            return $this->root = $this->kirby()->root('content') . '/' . $parent->diruri() . '/' . $file->filename();
        }

        if (is_a($parent, Site::class) === true) {
            return $this->root = $this->kirby()->root('content') . '/' . $file->filename();
        }

        throw new Exception('Unexpected model type');
    }

    public function storeFile(): string
    {
        return $this->root() . '.' . $this->extension();
    }

    public function update(array $values = [], array $strings = [])
    {
        $file = parent::update($values, $strings);

        if ($this->exists() === false) {
            return $file;
        }

        if (empty($strings) === false) {
            if (Data::write($this->storeFile(), $file->content()->toArray()) !== true) {
                throw new Exception('The file content could not be updated');
            }
        }

        return $file;
    }

}
