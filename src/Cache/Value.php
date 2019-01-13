<?php

namespace Kirby\Cache;

use Throwable;

/**
 * Cache Value
 * Stores the value, creation timestamp and expiration timestamp
 * and makes it possible to store all three with a single cache key.
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Value
{

    /**
     * the cached value
     * @var mixed
     */
    protected $value;

    /**
     * the expiration timestamp
     * @var int
     */
    protected $expires;

    /**
     * the creation timestamp
     * @var int
     */
    protected $created;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param int   $minutes the number of minutes until the value expires
     * @param int   $created the unix timestamp when the value has been created
     */
    public function __construct($value, int $minutes = 0, $created = null)
    {
        // keep forever if minutes are not defined
        if ($minutes === 0) {
            $minutes = 2628000;
        }

        $this->value   = $value;
        $this->minutes = $minutes;
        $this->created = $created ?? time();
    }

    /**
     * Returns the creation date as UNIX timestamp
     *
     * @return int
     */
    public function created(): int
    {
        return $this->created;
    }

    /**
     * Returns the expiration date as UNIX timestamp
     *
     * @return int
     */
    public function expires(): int
    {
        return $this->created + ($this->minutes * 60);
    }

    /**
     * Creates a value object from an array
     *
     * @param array $array
     * @return array
     */
    public static function fromArray(array $array): self
    {
        return new static($array['value'] ?? null, $array['minutes'] ?? 0, $array['created'] ?? null);
    }

    /**
     * Creates a value object from a json string
     *
     * @param string $json
     * @return array
     */
    public static function fromJson($json): self
    {
        try {
            $array = json_decode($json, true) ?? [];
        } catch (Throwable $e) {
            $array = [];
        }

        return static::fromArray($array);
    }

    /**
     * Convert the object to a json string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'minutes' => $this->minutes,
            'value'   => $this->value,
        ];
    }

    /**
     * Returns the value
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
