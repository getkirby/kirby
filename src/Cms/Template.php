<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Tpl;

/**
 * Represents a Kirby template and takes care
 * of loading the correct file.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Template
{
    /**
     * Global template data
     *
     * @var array
     */
    public static $data = [];

    /**
     * The name of the template
     *
     * @var string
     */
    protected $name;

    /**
     * Template type (html, json, etc.)
     *
     * @var string
     */
    protected $type;

    /**
     * Default template type if no specific type is set
     *
     * @var string
     */
    protected $defaultType;

    /**
     * Creates a new template object
     *
     * @param string $name
     * @param string $type
     * @param string $defaultType
     */
    public function __construct(string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;
    }

    /**
     * Converts the object to a simple string
     * This is used in template filters for example
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Checks if the template exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->file());
    }

    /**
     * Returns the expected template file extension
     *
     * @return string
     */
    public function extension(): string
    {
        return 'php';
    }

    /**
     * Returns the default template type
     *
     * @return string
     */
    public function defaultType(): string
    {
        return $this->defaultType;
    }

    /**
     * Returns the place where templates are located
     * in the site folder and and can be found in extensions
     *
     * @return string
     */
    public function store(): string
    {
        return 'templates';
    }

    /**
     * Detects the location of the template file
     * if it exists.
     *
     * @return string|null
     */
    public function file(): ?string
    {
        if ($this->hasDefaultType() === true) {
            try {
                // Try the default template in the default template directory.
                return F::realpath($this->root() . '/' . $this->name() . '.' . $this->extension(), $this->root());
            } catch (Exception $e) {
                // ignore errors, continue searching
            }

            // Look for the default template provided by an extension.
            $path = App::instance()->extension($this->store(), $this->name());

            if ($path !== null) {
                return $path;
            }
        }

        $name = $this->name() . '.' . $this->type();

        try {
            // Try the template with type extension in the default template directory.
            return F::realpath($this->root() . '/' . $name . '.' . $this->extension(), $this->root());
        } catch (Exception $e) {
            // Look for the template with type extension provided by an extension.
            // This might be null if the template does not exist.
            return App::instance()->extension($this->store(), $name);
        }
    }

    /**
     * Returns the template name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param array $data
     * @return string
     */
    public function render(array $data = []): string
    {
        return Tpl::load($this->file(), $data);
    }

    /**
     * Returns the root to the templates directory
     *
     * @return string
     */
    public function root(): string
    {
        return App::instance()->root($this->store());
    }

    /**
     * Returns the template type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Checks if the template uses the default type
     *
     * @return bool
     */
    public function hasDefaultType(): bool
    {
        $type = $this->type();

        return $type === null || $type === $this->defaultType();
    }
}
