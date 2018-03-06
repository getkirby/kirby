<?php

namespace Kirby\Cms;

use Exception;
use ReflectionClass;
use Kirby\Http\Response\Redirect;
use Kirby\Util\F;
use Kirby\Util\Str;

class Resource extends Model
{

    protected $path;
    protected $src;
    protected $type;
    protected $timestamp = false;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public function dir(): string
    {
        return dirname($this->root());
    }

    public function filename(): string
    {
        $name      = Str::slug(F::name($this->src()));
        $extension = F::extension($this->src());

        if ($this->timestamp() === true) {
            return $name . '.' . filemtime($this->src()) . '.' . $extension;
        }

        return $name . '.' . $extension;
    }

    public static function for(Model $model)
    {
        // Site and Page files
        if (is_a($model, File::class) === true) {
            return static::forFile($model);
        }

        // Avatars
        if (is_a($model, Avatar::class) === true) {
            return static::forAvatar($model);
        }

        throw new Exception('Invalid model type');
    }

    public static function forFile(File $file): self
    {
        if (is_a($file->parent(), Site::class) === true) {
            $path = 'site';
        } else {
            $path = 'pages/' . $file->parent()->id();
        }

        return new static([
            'kirby' => $file->kirby(),
            'path'  => $path,
            'src'   => $file->root(),
            'type'  => 'file',
        ]);
    }

    public static function forAvatar(Avatar $avatar): self
    {
        return new static([
            'kirby' => $avatar->kirby(),
            'path'  => 'users/' . $avatar->user()->id(),
            'src'   => $avatar->root(),
            'type'  => 'avatar'
        ]);
    }

    public static function forClassAsset(string $className, string $file, array $props = [])
    {
        if (file_exists($file) === false) {

            $reflector = new ReflectionClass($className);
            $directory = dirname($reflector->getFileName());
            $file      = $directory . '/' . $file;

            if (F::exists($file, $directory) === false) {
                throw new Exception(sprintf('Invalid asset "%s" in class "%s"', $file, $className));
            }
        }

        return new static(array_merge([
            'path' => Str::slug($className),
            'src'  => $file,
            'type' => 'asset'
        ], $props));
    }

    public function id(): string
    {
        return $this->path() . '/' . $this->filename();
    }

    public function link(string $root = null): self
    {
        if (F::link($this->src(), $this->root()) !== true) {
            throw new Exception('The resource could not be linked');
        }

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function redirect()
    {
        return new Redirect($this->url(), 307);
    }

    public function root(): string
    {
        return $this->kirby()->root('media') . '/' . $this->id();
    }

    protected function setPath(string $path): self
    {
        $this->path = trim(strtolower($path), '/');
        return $this;
    }

    protected function setSrc(string $src): self
    {
        $this->src = $src;
        return $this;
    }

    protected function setTimestamp(bool $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    protected function setType(string $type = null): self
    {
        $this->type = $type;
        return $this;
    }

    public function src(): string
    {
        return $this->src;
    }

    public function timestamp(): bool
    {
        return $this->timestamp;
    }

    public function type()
    {
        return $this->type;
    }

    public function url(): string
    {
        return $this->kirby()->url('media') .'/' . $this->id();
    }

    public function unlink(): bool
    {
        return F::remove($this->root());
    }

}
