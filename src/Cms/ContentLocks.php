<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

use Throwable;

/**
 * ContentLocks
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class ContentLocks
{

    /**
     * The data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Returns path to a model's lock file
     *
     * @param ModelWithContent $model
     * @return string
     */
    public static function file(ModelWithContent $model): string
    {
        return $model->contentFileDirectory() . '/.lock';
    }

    public function get(ModelWithContent $model): array
    {
        $file = static::file($model);
        $id   = static::id($model);

        if (
            isset($this->data[$file]) === true &&
            isset($this->data[$file][$id]) === true
        ) {
            return $this->data[$file][$id];
        }

        try {
            $data = Data::read($file, 'yaml');
        } catch (Throwable $th) {
            $data = [];
        }

        $this->data[$file] = $data;

        return $this->data[$file][$id] ?? [];
    }

    /**
     * Returns prepended model id
     *
     * @param ModelWithContent $model
     * @return string
     */
    public static function id(ModelWithContent $model): string
    {
        return '/' . $model->id();
    }

    public function set(ModelWithContent $model, array $data): bool
    {
        $file = static::file($model);
        $id   = static::id($model);

        $this->data[$file][$id] = $data;

        // make sure to unset model id entries,
        // if no lock data for the model exists
        foreach ($this->data[$file] as $id => $data) {
            if (
                isset($data['lock']) === false &&
                (isset($data['unlock']) === false ||
                count($data['unlock']) === 0)
            ) {
                unset($this->data[$file][$id]);
            } elseif (
                isset($data['unlock']) === true &&
                count($data['unlock']) === 0
            ) {
                unset($this->data[$file][$id]['unlock']);
            }
        }

        if (count($this->data[$file]) === 0) {
            return F::remove($file);
        }

        return Data::write($file, $this->data[$file], 'yaml');
    }
}
