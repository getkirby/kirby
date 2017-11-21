<?php

namespace Kirby\Text;

use Exception;
use Kirby\Toolkit\DI\Singletons;

/**
 * The Tags Parser parses tags in
 * plain text, which are easy to write for
 * editors and easy to write and extend.
 *
 * Tags look like this (image: myimage.jpg alt: my image)
 * They are intended as a more editor-friendly alternative
 * to HTML or even some more complex Markdown tags.
 *
 * Custom Tags can be injected into the parser with the
 * set method and default tags can also be overwritten that way.
 *
 * Custom Tag classes must be subclasses of Kirby\Text\Tags\Tag
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Tags
{

    /**
     * All registered Tag classes
     *
     * @var Singletons
     */
    protected $tags;

    /**
     * Creates a new KirbyText Parser
     * The constructor sets all default
     * tag classes. Those can be overwritten
     * afterwards with the set method
     */
    public function __construct()
    {
        $this->tags = new Singletons;

        // set default tags
        $this->tags->set('date', 'Kirby\Text\Tags\Tag\Date');
        $this->tags->set('link', 'Kirby\Text\Tags\Tag\Link');
        $this->tags->set('email', 'Kirby\Text\Tags\Tag\Email');
        $this->tags->set('file', 'Kirby\Text\Tags\Tag\File');
        $this->tags->set('gist', 'Kirby\Text\Tags\Tag\Gist');
        $this->tags->set('image', 'Kirby\Text\Tags\Tag\Image');
        $this->tags->set('tel', 'Kirby\Text\Tags\Tag\Tel');
        $this->tags->set('twitter', 'Kirby\Text\Tags\Tag\Twitter');
        $this->tags->set('vimeo', 'Kirby\Text\Tags\Tag\Vimeo');
        $this->tags->set('youtube', 'Kirby\Text\Tags\Tag\Youtube');
    }

    /**
     * Defines a new tag handling class, which
     * has to be an extension of Kirby\Text\Tags\Tag
     * Default tags, which are defined in the
     * constructor can be overwritten that way.
     *
     * @param  string $name
     * @param  string $class
     * @return Tags
     */
    public function set(string $name, string $class): self
    {
        if (is_subclass_of($class, 'Kirby\Text\Tags\Tag') === false) {
            throw new Exception('Tags must be a subclass of Kirby\Text\Tags\Tag');
        }

        $this->tags->set($name, $class);
        return $this;
    }

    /**
     * Takes a string and parses all tags within
     * the string, which can be handled by the
     * registered tag classes.
     *
     * @param  string $text
     * @return string
     */
    public function parse(string $text): string
    {
        return preg_replace_callback('!(?=[^\]])\([a-z0-9_-]+:.*?\)!is', function ($match) {
            try {
                return $this->tag($match[0]);
            } catch (Exception $e) {
                return $match[0];
            }
        }, $text);
    }

    /**
     * Parses a given tag, which can be
     * passed as string or array.
     *
     * 1. String Example
     * ```
     * (image: myimage.jpg alt: my image)
     * ```
     *
     * 2. Array Example
     * ```
     * [
     *     'image' => 'myimage',
     *     'alt'   => 'my image'
     * ]
     * ```
     *
     * @param  string|array $input
     * @return string
     */
    public function tag($input): string
    {
        if (is_string($input) === true) {
            return $this->tagFromString($input);
        } elseif (is_array($input) === true) {
            return $this->tagFromArray($input);
        } else {
            throw new Exception('Invalid tag input');
        }
    }

    /**
     * Parses a tag string
     * ```
     * (image: myimage.jpg alt: my image)
     * ```
     *
     * @param  string $string
     * @return string
     */
    public function tagFromString(string $string): string
    {
        // remove the brackets
        $tag        = trim(rtrim(ltrim($string, '('), ')'));
        $name       = trim(substr($tag, 0, strpos($tag, ':')));
        $instance   = $this->tags->get($name);
        $attributes = [];

        // extract all attributes
        $search = preg_split('!(' . implode('|', array_merge([$name], $instance->attributes())) . '):!i', $tag, false, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $num    = 0;

        foreach ($search as $key) {
            if (!isset($search[$num + 1])) {
                break;
            }
            $key   = trim($search[$num]);
            $value = trim($search[$num + 1]);
            $attributes[$key] = $value;
            $num = $num + 2;
        }

        $value = array_shift($attributes);

        return $instance->parse($value, $attributes);
    }

    /**
     * Parses a tag array
     * ```
     * [
     *     'image' => 'myimage',
     *     'alt'   => 'my image'
     * ]
     * ```
     *
     * @param  array   $attributes
     * @return string
     */
    public function tagFromArray(array $attributes): string
    {
        $name     = key($attributes);
        $value    = array_shift($attributes);
        $instance = $this->tags->get($name);

        return $instance->parse($value, $attributes);
    }
}
