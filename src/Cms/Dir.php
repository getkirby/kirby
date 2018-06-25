<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;

/**
 * Extension of the Toolkit Dir class with a new
 * Dir::inventory method, that handles scanning directories
 * and converts the results into our children, files and
 * other page stuff.
 */
class Dir extends \Kirby\Toolkit\Dir
{
    public static $numSeparator = '_';

    public static function inventory(string $dir, string $content = 'txt'): array
    {
        $dir = realpath($dir);

        $inventory = [
            'children' => [],
            'files'    => [],
            'template' => 'default',
            'content'  => null,
        ];

        if ($dir === false) {
            return $inventory;
        }

        $items = Dir::read($dir);

        natsort($items);

        foreach ($items as $item) {

            // ignore all items with a leading dot
            if (in_array(substr($item, 0, 1), ['.', '_']) === true) {
                continue;
            }

            $root = $dir . '/' . $item;

            if (is_dir($root) === true) {

                // extract the slug and num of the directory
                if (preg_match('/^([0-9]+)' . static::$numSeparator . '(.*)$/', $item, $match)) {
                    $num  = $match[1];
                    $slug = $match[2];
                } else {
                    $num  = null;
                    $slug = $item;
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

        return $inventory;
    }
}
