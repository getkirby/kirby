<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Component;

/**
 * Section
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Section extends Component
{
    /**
     * Registry for all component mixins
     *
     * @var array
     */
    public static $mixins = [];

    /**
     * Registry for all component types
     *
     * @var array
     */
    public static $types = [];


    /**
     * Section constructor.
     *
     * @param string $type
     * @param array $attrs
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function __construct(string $type, array $attrs = [])
    {
        if (isset($attrs['model']) === false) {
            throw new InvalidArgumentException('Undefined section model');
        }

        if (is_a($attrs['model'], 'Kirby\Cms\Model') === false) {
            throw new InvalidArgumentException('Invalid section model');
        }

        // use the type as fallback for the name
        $attrs['name'] = $attrs['name'] ?? $type;
        $attrs['type'] = $type;

        parent::__construct($type, $attrs);
    }

    public function errors(): array
    {
        if (array_key_exists('errors', $this->methods) === true) {
            return $this->methods['errors']->call($this);
        }

        return $this->errors ?? [];
    }

    /**
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->model()->kirby();
    }

    /**
     * @return \Kirby\Cms\Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['model']);

        return $array;
    }

    /**
     * @return array
     */
    public function toResponse(): array
    {
        return array_merge([
            'status' => 'ok',
            'code'   => 200,
            'name'   => $this->name,
            'type'   => $this->type
        ], $this->toArray());
    }
}
