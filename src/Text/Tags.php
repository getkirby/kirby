<?php

namespace Kirby\Text;

use Exception;

use Kirby\Text\Tags\Tag;
use Kirby\Text\Tags\Date;
use Kirby\Text\Tags\Link;
use Kirby\Text\Tags\Email;
use Kirby\Text\Tags\File;
use Kirby\Text\Tags\Gist;
use Kirby\Text\Tags\Image;
use Kirby\Text\Tags\Tel;
use Kirby\Text\Tags\Twitter;
use Kirby\Text\Tags\Vimeo;
use Kirby\Text\Tags\Youtube;

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
     * @var array
     */
    protected $tags = [
        'date'    => Date::class,
        'link'    => Link::class,
        'email'   => Email::class,
        'file'    => File::class,
        'gist'    => Gist::class,
        'image'   => Image::class,
        'tel'     => Tel::class,
        'twitter' => Twitter::class,
        'vimeo'   => Vimeo::class,
        'youtube' => Youtube::class,
    ];

    /**
     * Instance cache
     *
     * @var array
     */
    protected $tagInstances = [];

    public function __construct(array $tags = [])
    {
        $tags = array_merge($this->tags, $tags);

        foreach ($tags as $name => $class) {
            if (is_subclass_of($class, Tag::class) === false) {
                throw new Exception('Tags must be a subclass of Kirby\Text\Tags\Tag: ' . $name . ' => ' . $class);
            }
        }

        $this->tags = $tags;
    }

    /**
     * Takes a string and parses all tags within
     * the string, which can be handled by the
     * registered tag classes.
     *
     * @param  string $text
     * @param  array $data Additional data that will be passed down to the tag class
     * @return string
     */
    public function parse(string $text, array $data = []): string
    {
        return preg_replace_callback('!(?=[^\]])\([a-z0-9_-]+:.*?\)!is', function ($match) use ($data) {
            try {
                return $this->tag($match[0], $data);
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
     * @param  array $data
     * @return string
     */
    public function tag($input, array $data = []): string
    {
        if (is_string($input) === true) {
            return $this->tagFromString($input, $data);
        } elseif (is_array($input) === true) {
            return $this->tagFromArray($input, $data);
        } else {
            throw new Exception('Invalid tag input');
        }
    }

    /**
     * Creates a tag class instance
     *
     * @param string $name
     * @return void
     */
    protected function tagInstance(string $name)
    {
        if (isset($this->tagInstances[$name]) === true) {
            return $this->tagInstances[$name];
        }

        if (isset($this->tags[$name]) === false) {
            throw new Exception('Unsupported tag: ' . $name);
        }

        return $this->tagInstances[$name] = new $this->tags[$name]();
    }

    /**
     * Parses a tag string
     * ```
     * (image: myimage.jpg alt: my image)
     * ```
     *
     * @param  string $string
     * @param  array $data
     * @return string
     */
    public function tagFromString(string $string, array $data): string
    {
        // remove the brackets
        $tag        = trim(rtrim(ltrim($string, '('), ')'));
        $name       = trim(substr($tag, 0, strpos($tag, ':')));
        $instance   = $this->tagInstance($name);
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

        return $instance->parse($value, $attributes, $data);
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
     * @param  array   $data
     * @return string
     */
    public function tagFromArray(array $attributes, $data): string
    {
        $name     = key($attributes);
        $value    = array_shift($attributes);
        $instance = $this->tagInstance($name);

        return $instance->parse($value, $attributes, $data);
    }
}
