<?php

namespace Kirby\Cache;

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
     */
    public function __construct($value, int $minutes = 0)
    {

        // keep forever if minutes are not defined
        if ($minutes === 0) {
            $minutes = 2628000;
        }

        // take the current time
        $time = time();

        $this->value   = $value;
        $this->expires = $time + ($minutes * 60);
        $this->created = $time;
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

    /**
     * Returns the expiration date as UNIX timestamp
     *
     * @return int
     */
    public function expires(): int
    {
        return $this->expires;
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
}
