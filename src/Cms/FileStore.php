<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

class FileStore
{

    protected $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function content(): Content
    {
        $content = Data::read($this->file->root() . '.txt');
        return new Content($content, $this->file);
    }

    public function delete(): bool
    {
        // delete the meta file first
        if (file_exists($txt = $this->file->root() . '.txt') === true) {
            unlink($txt);
        }

        // delete all public versions
        App::instance()->media()->delete($this->file->model(), $this->file);

        // delete the real thing
        if (file_exists($this->file->root()) === true) {
            unlink($this->file->root()) !== false;
        }

        return true;
    }

    public function rename(string $name): File
    {
        $media      = App::instance()->media();
        $content    = $this->file->content()->toArray();
        $filename   = $name . '.' . $this->file->extension();
        $props      = [
            'id'   => $id = ltrim($this->file->model()->id() . '/' . $filename, '/'),
            'root' => $this->file->model()->root() . '/' . $filename,
            'url'  => $this->this->media()->url($this->file->model()) . '/' . $filename
        ];

        // remove the content file first
        if (file_exists($txt = $this->file->root() . '.txt')) {
            unlink($txt);
        }

        // remove all public versions
        $media->delete($this->file->model(), $this->file);

        // rename the file
        rename($this->file->root(), $props['root']);

        // create a clean file object for it
        $this->file = $this->file->clone($props);

        // create a new public version
        $media->create($this->file->model(), $this->file);

        // store the content in a fresh content file
        if (empty($content) === false) {
            $this->file->update($content);
        }

        return $this->file;
    }

    public function replace(string $source): File
    {
        if (file_exists($source) === false) {
            throw new Exception(sprintf('The source file "%s" does not exist', $source));
        }

        $media = App::instance()->media();

        // create a temporary image object to run validations
        $this->rules()->check('file.replace', $this->file, new Image($source, '/tmp'));

        // delete all public versions
        $media->delete($this->file->model(), $this->file);

        // overwrite the original
        copy($source, $this->file->root());

        // create a new public file
        $media->create($this->file->model(), $file);

        return $this->file;
    }

    public function update(array $content = []): File
    {
        $content = $this->file->content()->update($content);
        Data::write($this->file->root() . '.txt', $content->toArray());

        return $this->file->setContent($content);
    }

}
