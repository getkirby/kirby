<?php

namespace Kirby\Cms;

use ReflectionClass;
use Kirby\Http\Response\Redirect;
use Kirby\Util\F;
use Kirby\Util\Str;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;

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

    public function extension() {
        return F::extension($this->src());
    }

    public function filename($raw = false): string
    {
        $name      = Str::slug(F::name($this->src()));
        $extension = F::extension($this->src());

        if ($this->timestamp() === true && $raw === false) {
            return $name . '.' . filemtime($this->src()) . '.' . $extension;
        }

        return $name . '.' . $extension;
    }

    public static function for(Model $model, ...$arguments)
    {
        // Site and Page files
        if (is_a($model, File::class) === true) {
            return static::forFile($model);
        }

        // Avatars
        if (is_a($model, Avatar::class) === true) {
            return static::forAvatar($model);
        }

        // Plugin
        if (is_a($model, Plugin::class) === true) {
            return static::forPlugin($model, ...$arguments);
        }

        throw new InvalidArgumentException('Invalid model type: ' . get_class($type));
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

    public static function forPlugin(Plugin $plugin, string $path): self
    {

        $path = static::stripTimestamp($path);

        return new static([
            'kirby'     => $plugin->kirby(),
            'path'      => 'plugins/' . $plugin->name() . '/' . ltrim(dirname($path), './'),
            'src'       => $plugin->root() . '/assets/' . $path,
            'type'      => 'plugin',
            'timestamp' => true
        ]);
    }

    public static function forClassAsset(string $className, string $file, array $props = [])
    {
        if (file_exists($file) === false) {

            $reflector = new ReflectionClass($className);
            $directory = dirname($reflector->getFileName());
            $file      = $directory . '/' . $file;

            if (F::exists($file, $directory) === false) {
                throw new InvalidArgumentException(sprintf('Invalid asset "%s" in class "%s"', $file, $className));
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
        $this->purge();

        if (F::link($this->src(), $this->root()) !== true) {
            throw new LogicException('The resource could not be linked');
        }

        return $this;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Cleans up all instances of the current
     * resource, including all timestamped versions
     *
     * @return bool
     */
    public function purge(): bool
    {
        $dir   = $this->dir();
        $name  = F::name($this->filename(true));
        $ext   = $this->extension();
        $files = glob($dir . '/' . $name . '.*.' . $ext);

        foreach ($files as $file) {
            F::remove($file);
        }

        F::remove($this->root());

        return true;
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

    public static function stripTimestamp(string $path): string
    {
        // strip the timestamp
        return preg_replace('!([0-9]+?)\.([a-z]{2,4}?)$!', '$2', $path);
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
