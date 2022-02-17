<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Filesystem\F;
use Throwable;

/**
 * Simple PHP view engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class View
{
    /**
     * The absolute path to the view file
     *
     * @var string
     */
    protected $file;

    /**
     * The view data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Creates a new view object
     *
     * @param string $file
     * @param array $data
     */
    public function __construct(string $file, array $data = [])
    {
        $this->file = $file;
        $this->data = $data;
    }

    /**
     * Returns the view's data array
     * without globals.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Checks if the template file exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return is_file($this->file()) === true;
    }

    /**
     * Returns the view file
     *
     * @return string|false
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Creates an error message for the missing view exception
     *
     * @return string
     */
    protected function missingViewMessage(): string
    {
        return 'The view does not exist: ' . $this->file();
    }

    /**
     * Renders the view
     *
     * @return string
     */
    public function render(): string
    {
        if ($this->exists() === false) {
            throw new Exception($this->missingViewMessage());
        }

        ob_start();

        $exception = null;
        try {
            F::load($this->file(), null, $this->data());
        } catch (Throwable $e) {
            $exception = $e;
        }

        $content = ob_get_contents();
        ob_end_clean();

        if ($exception === null) {
            return $content;
        }

        throw $exception;
    }

    /**
     * Alias for View::render()
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->render();
    }

    /**
     * Magic string converter to enable
     * converting view objects to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
