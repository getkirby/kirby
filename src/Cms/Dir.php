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

    /**
     * Scans the directory and analyzes files,
     * content, meta info and children. This is used
     * in Page, Site and User objects to fetch all
     * relevant information.
     *
     * @param string $dir
     * @param string $contentExtension
     * @param array $contentIgnore
     * @param boolean $multilang
     * @return array
     */
    public static function inventory(string $dir, string $contentExtension = 'txt', array $contentIgnore = null, bool $multilang = false): array
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

        $items = Dir::read($dir, $contentIgnore);

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
                    'dirname' => $item,
                    'model'   => null,
                    'num'     => $num,
                    'root'    => $root,
                    'slug'    => $slug,
                ];
            } else {
                $extension = pathinfo($item, PATHINFO_EXTENSION);

                switch ($extension) {
                    case 'htm':
                    case 'html':
                    case 'php':
                        // don't track those files
                        break;
                    case $contentExtension:
                        $content[] = pathinfo($item, PATHINFO_FILENAME);
                        break;
                    default:
                        $inventory['files'][$item] = [
                            'filename'  => $item,
                            'extension' => $extension,
                            'root'      => $root,
                        ];
                }
            }
        }

        // remove the language codes from all content filenames
        if ($multilang === true) {
            foreach ($content as $key => $filename) {
                $content[$key] = pathinfo($filename, PATHINFO_FILENAME);
            }

            $content = array_unique($content);
        }

        $inventory = static::inventoryContent($dir, $inventory, $content);
        $inventory = static::inventoryModels($inventory, $contentExtension, $multilang);

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
     * @param string $contentExtension
     * @param bool $multilang
     * @return array
     */
    protected static function inventoryModels(array $inventory, string $contentExtension, bool $multilang = false): array
    {
        // inject models
        if (empty($inventory['children']) === false && empty(Page::$models) === false) {
            if ($multilang === true) {
                $contentExtension = App::instance()->defaultLanguage()->code() . '.' . $contentExtension;
            }

            foreach ($inventory['children'] as $key => $child) {
                foreach (Page::$models as $modelName => $modelClass) {
                    if (file_exists($child['root'] . '/' . $modelName . '.' . $contentExtension) === true) {
                        $inventory['children'][$key]['model'] = $modelName;
                        break;
                    }
                }
            }
        }

        return $inventory;
    }
}
