<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;

class Dir extends \Kirby\Toolkit\Dir
{

    protected static $inventory = [];

    public static function inventory(string $dir, string $content = 'txt'): array
    {
        if (isset(static::$inventory[$dir]) === true) {
            return static::$inventory[$dir];
        }

        $dir = realpath($dir);

        if ($dir === false) {
            throw new Exception('The directory does not exist');
        }

        $inventory = [
            'children' => [],
            'files'    => [],
            'template' => 'default',
            'content'  => null,
        ];

        foreach (static::read($dir) as $item) {

            // ignore all items with a leading dot
            if (in_array(substr($item, 0, 1), ['.', '_']) === true) {
                continue;
            }

            $root = $dir . '/' . $item;

            if (is_dir($root) === true) {

                $dot  = strpos($item, '.');
                $num  = null;
                $slug = $item;

                if ($dot !== false) {
                    $num  = substr($item, 0, $dot);
                    $slug = substr($item, $dot + 1);
                }

                $inventory['children'][] = [
                    'root' => $root,
                    'num'  => $num,
                    'slug' => $slug,
                ];

            } else {

                $extension = pathinfo($item, PATHINFO_EXTENSION);

                if ($extension === $content) {
                    $basename  = pathinfo($item, PATHINFO_FILENAME);
                    $extension = pathinfo($basename, PATHINFO_EXTENSION);

                    if (empty($extension) === true) {
                        $inventory['template'] = $basename;
                        $inventory['content']  = $root;
                    }
                } else {
                    $inventory['files'][$item] = [
                        'filename'  => $item,
                        'extension' => $extension,
                        'root'      => $root,
                    ];
                }

            }

        }

        // inject models
        if (empty($inventory['children']) === false && empty(Page::$models) === false) {

            $glob = '{' . implode(',', array_keys(Page::$models)) . '}.' . $content;

            foreach ($inventory['children'] as $key => $child) {
                $modelFile = glob($child['root'] . '/' . $glob, GLOB_BRACE)[0] ?? null;
                $inventory['children'][$key]['model'] = $modelFile ? pathinfo($modelFile, PATHINFO_FILENAME) : null;
            }
        }

        return static::$inventory[$dir] = $inventory;
    }

}
