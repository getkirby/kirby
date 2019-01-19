<?php

namespace Kirby\Cms;

use stdClass;
use ReflectionMethod;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Str;

/**
 * Foundation for Page, Site, File and User models.
 */
abstract class Model
{
    use Properties;

    /**
     * The parent Kirby instance
     *
     * @var App
     */
    public static $kirby;

    /**
     * The parent Site instance
     *
     * @var Site
     */
    protected $site;

    /**
     * Makes it possible to convert the entire model
     * to a string. Mostly useful for debugging
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id();
    }

    /**
     * Each model must return a unique id
     *
     * @return string|int
     */
    public function id()
    {
        return null;
    }

    /**
     * Returns the parent Kirby instance
     *
     * @return App|null
     */
    public function kirby(): App
    {
        return static::$kirby = static::$kirby ?? App::instance();
    }

    /**
     * Returns the parent Site instance
     *
     * @return Site|null
     */
    public function site()
    {
        return $this->site = $this->site ?? $this->kirby()->site();
    }

    /**
     * Setter for the parent Kirby object
     *
     * @param Kirby|null $kirby
     * @return self
     */
    protected function setKirby(App $kirby = null)
    {
        static::$kirby = $kirby;
        return $this;
    }

    /**
     * Setter for the parent Site object
     *
     * @internal
     * @param Site|null $site
     * @return self
     */
    public function setSite(Site $site = null)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * Convert the model to a simple array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->propertiesToArray();
    }
}
