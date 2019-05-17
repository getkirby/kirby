<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Properties;

/**
 * Foundation for Page, Site, File and User models.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
abstract class Model
{
    use Properties;

    /**
     * The parent Kirby instance
     *
     * @var Kirby\Cms\App
     */
    public static $kirby;

    /**
     * The parent site instance
     *
     * @var Kirby\Cms\Site
     */
    protected $site;

    /**
     * Makes it possible to convert the entire model
     * to a string. Mostly useful for debugging
     *
     * @return string
     */
    public function __toString(): string
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
     * @return Kirby\Cms\App
     */
    public function kirby()
    {
        return static::$kirby = static::$kirby ?? App::instance();
    }

    /**
     * Returns the parent Site instance
     *
     * @return Kirby\Cms\Site
     */
    public function site()
    {
        return $this->site = $this->site ?? $this->kirby()->site();
    }

    /**
     * Setter for the parent Kirby object
     *
     * @param Kirby\Cms\App|null $kirby
     * @return self
     */
    protected function setKirby(App $kirby = null)
    {
        static::$kirby = $kirby;
        return $this;
    }

    /**
     * Setter for the parent site object
     *
     * @internal
     * @param Kirby\Cms\Site|null $site
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
