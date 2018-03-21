<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Util\Dir;
use Kirby\Util\Properties;

class Base
{
    use Properties;

    protected $extension;
    protected $inventory;
    protected $type;
    protected $root;
    protected $ignore;

    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }

    public function children(): array
    {
        return $this->inventory()['children'];
    }

    protected function defaultExtension(): string
    {
        return 'txt';
    }

    protected function defaultIgnore(): array
    {
        return [
            'Thumb.db',
            '@eaDir'
        ];
    }

    public function delete(): bool
    {
        if (Dir::remove($this->root()) !== true) {
            throw new Exception('The directory could not be deleted');
        }

        return true;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function files(): array
    {
        return $this->inventory()['files'];
    }

    public function inventory(): array
    {
        if (is_array($this->inventory) === true) {
            return $this->inventory;
        }

        $this->inventory = [
            'children' => [],
            'drafts'   => [],
            'files'    => [],
            'type'     => 'default',
        ];

        $root = $this->root();

        if (is_dir($root) === false) {
            return $this->inventory;
        }

        $scan = scandir($root);

        foreach ($scan as $item) {
            if (in_array($item, $this->ignore) === true) {
                continue;
            }

            // ignore all items with a leading dot
            if (in_array(substr($item, 0, 1), ['.', '_']) === true) {
                continue;
            }

            // build the full root to the item
            $itemRoot = $root . '/' . $item;

            // handle directories
            if (is_dir($itemRoot) === true) {

                // find the first dot
                $dot = strpos($item, '.');

                if ($dot !== false) {
                    $num  = intval(substr($item, 0, $dot));
                    $slug = substr($item, $dot + 1);
                } else {
                    $num  = null;
                    $slug = $item;
                }

                $this->inventory['children'][$slug] = [
                    'num'  => $num,
                    'root' => $itemRoot,
                ];
            } else {
                $extension = pathinfo($item, PATHINFO_EXTENSION);

                if ($extension === $this->extension()) {
                    $name = pathinfo($item, PATHINFO_FILENAME);
                    $ext  = pathinfo($name, PATHINFO_EXTENSION);

                    if (empty($ext) === false) {
                        continue;
                    }

                    $this->inventory['type'] = $name;
                } else {
                    $this->inventory['files'][$item] = [
                        'filename'  => $item,
                        'extension' => $extension,
                        'root'      => $itemRoot,
                    ];
                }
            }
        }

        return $this->inventory;
    }

    public function read(): array
    {
        return Data::read($this->storage());
    }

    public function root(): string
    {
        return $this->root;
    }

    protected function setExtension(string $extension = 'txt')
    {
        $this->extension = $extension;
        return $this;
    }

    protected function setIgnore(array $ignore = null)
    {
        $this->ignore = $ignore;
        return $this;
    }

    protected function setRoot(string $root)
    {
        $this->root = realpath($root);
        return $this;
    }

    protected function setType(string $type = null)
    {
        $this->type = $type;
        return $this;
    }

    public function storage(): string
    {
        return $this->root() . '/' . $this->type() . '.' . $this->extension();
    }

    public function type(): string
    {
        return $this->type ?? $this->inventory()['type'];
    }

    public function write(array $content = []): bool
    {
        return Data::write($this->storage(), $content);
    }
}
