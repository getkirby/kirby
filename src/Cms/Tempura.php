<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Query;

/**
 * The Tempura class can be used to
 * replace template strings with data from arrays and objects,
 * including object methods.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Tempura
{

    /**
     * The template string
     *
     * @var string
     */
    protected $template;

    /**
     * Template data
     *
     * @var array
     */
    protected $data;

    /**
     * Creates a new Tempura template object
     *
     * @param string $template
     * @param array $data
     */
    public function __construct(string $template = null, $data = [])
    {
        $this->template = $template;
        $this->data     = $data;
    }

    /**
     * Renders the template string and replaces all the
     * variables in curly braces with values from the passed
     * data objects or arrays
     *
     * @return string
     */
    public function render(): string
    {
        return preg_replace_callback('!{{(.*?)}}!', function ($match) {
            return (new Query($match[1], $this->data))->result();
        }, $this->template);
    }

    /**
     * Makes it possible to echo the Tempura
     * object instead of calling the render method on it.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
