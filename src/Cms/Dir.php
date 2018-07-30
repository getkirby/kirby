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

    public static function inventory(string $dir, string $contentExtension = 'txt'): array
    {
        $dir = realpath($dir);

        $inventory = [
            'children' => [],
            'files'    => [],
            'template' => 'default',
        ];

        if ($dir === false) {
            return $inventory;
        }

        $items = Dir::read($dir);

        // a temporary store for all content files
        $content = [];

        // sort all items naturally to avoid sorting issues later
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

                if ($extension === $contentExtension) {
                    $content[] = pathinfo($item, PATHINFO_FILENAME);
                } else {
                    $inventory['files'][$item] = [
                        'filename'  => $item,
                        'extension' => $extension,
                        'root'      => $root,
                    ];
                }
            }
        }

        $inventory = static::inventoryContent($dir, $inventory, $content);
        $inventory = static::inventoryModels($inventory, $contentExtension);

        return $inventory;
    }

    /**
     * Take all content files,
     * remove those who are meta files and
     * detect the main content file
     *
     * @param array $inventory
     * @param array $content
     * @return array
     */
    protected static function inventoryContent(string $dir, array $inventory, array $content): array
    {
        // filter meta files from the content file
        if (empty($content) === true) {
            $inventory['template'] = 'default';
            return $inventory;
        }

        foreach ($content as $contentName) {
            // could be a meta file. i.e. cover.jpg
            if (isset($inventory['files'][$contentName]) === true) {
                continue;
            }

            // it's most likely the template
            $inventory['template'] = $contentName;
        }

        return $inventory;
    }

    /**
     * Go through all inventory children
     * and inject a model for each
     *
     * @param array $inventory
     * @return array
     */
    protected static function inventoryModels(array $inventory, string $contentExtension): array
    {
        // inject models
        if (empty($inventory['children']) === false && empty(Page::$models) === false) {
            $glob = '{' . implode(',', array_keys(Page::$models)) . '}.' . $contentExtension;

            foreach ($inventory['children'] as $key => $child) {
                $modelFile = glob($child['root'] . '/' . $glob, GLOB_BRACE)[0] ?? null;
                $inventory['children'][$key]['model'] = $modelFile ? pathinfo($modelFile, PATHINFO_FILENAME) : null;
            }
        }

        return $inventory;
    }
}
