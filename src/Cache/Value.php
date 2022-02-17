<?php

namespace Kirby\Cache;

use Throwable;

/**
 * Cache Value
 * Stores the value, creation timestamp and expiration timestamp
 * and makes it possible to store all three with a single cache key
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Value
{
    /**
     * Cached value
     * @var mixed
     */
    protected $value;

    /**
     * the number of minutes until the value expires
     * @todo Rename this property to $expiry to reflect
     *       both minutes and absolute timestamps
     * @var int
     */
    protected $minutes;

    /**
     * Creation timestamp
     * @var int
     */
    protected $created;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param int $minutes the number of minutes until the value expires
     *                     or an absolute UNIX timestamp
     * @param int $created the UNIX timestamp when the value has been created
     */
    public function __construct($value, int $minutes = 0, int $created = null)
    {
        $this->value   = $value;
        $this->minutes = $minutes ?? 0;
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
     * Returns the expiration date as UNIX timestamp or
     * null if the value never expires
     *
     * @return int|null
     */
    public function expires(): ?int
    {
        // 0 = keep forever
        if ($this->minutes === 0) {
            return null;
        }

        if ($this->minutes > 1000000000) {
            // absolute timestamp
            return $this->minutes;
        }

        return $this->created + ($this->minutes * 60);
    }

    /**
     * Creates a value object from an array
     *
     * @param array $array
     * @return static
     */
    public static function fromArray(array $array)
    {
        return new static($array['value'] ?? null, $array['minutes'] ?? 0, $array['created'] ?? null);
    }

    /**
     * Creates a value object from a JSON string;
     * returns null on error
     *
     * @param string $json
     * @return static|null
     */
    public static function fromJson(string $json)
    {
        try {
            $array = json_decode($json, true);

            if (is_array($array)) {
                return static::fromArray($array);
            } else {
                return null;
            }
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Converts the object to a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the object to an array
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
     * Returns the pure value
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
