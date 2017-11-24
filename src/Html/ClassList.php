<?php

namespace Kirby\Html;

use Exception;

/**
 * The ClassList tries to be almost
 * identical to the ClassList implementation
 * in modern browsers. It helps to manage
 * and inspect class selectors for HTML
 * elements.
 *
 * @package   Kirby Html
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class ClassList
{

    /**
     * List of all registered class names
     *
     * @var array
     */
    protected $classNames = [];

    /**
     * Creates a new ClassList object
     *
     * @param array|string|list $classNames
     */
    public function __construct(...$classNames)
    {
        $this->add(...$classNames);
    }

    /**
     * Checks if a className exists in the ClassList
     *
     * @param  string $className
     * @return bool
     */
    public function contains(string $className): bool
    {
        return in_array(strtolower($className), array_map('strtolower', $this->classNames)) === true;
    }

    /**
     * Adds one or multiple class names to the list
     *
     * If one of the given class names contains a space
     * the class name will be split into multiple class names
     * and each one of them will be added individually.
     *
     * 1. Add a single class
     * `$classes->add('link')`
     *
     * 2. Add multiple classes
     * `$classes->add('link', 'link-primary')`
     *
     * 3. Add multiple classes as array
     * `$classes->add(['link', 'link-primary'])`
     *
     * 4. Add multiple classes separated with spaces
     * `$classes->add('link link-primary', 'btn')`
     * (will add three classes)
     *
     * @param   array|string|list $classNames
     * @return  ClassList
     */
    public function add(...$classNames): self
    {
        foreach ($classNames as $className) {
            if (is_array($className) === true) {
                $this->add(...$className);
            } elseif (is_string($className) === true) {
                $this->addByString($className);
            } else {
                throw new Exception('Invalid class name type');
            }
        }

        return $this;
    }

    /**
     * Helper which takes a string of one or
     * multiple classes to be added to the list
     *
     * @param string $className
     */
    protected function addByString(string $className)
    {
        // prepare the class name
        $className = trim($className);

        // are there still any spaces in the class name?
        if (strstr($className, ' ') !== false) {
            $names = explode(' ', $className);
            $this->add(...$names);
        } elseif (empty($className) === false) {
            $this->classNames[] = $className;
        }
    }

    /**
     * Removes one or multiple classNames from the list.
     * Parameters can be used as with ClassList::add()
     *
     * @param  string    $classNames
     * @return ClassList
     */
    public function remove(...$classNames): self
    {
        foreach ($classNames as $className) {
            if (is_array($className) === true) {
                $this->remove(...$className);
            } elseif (is_string($className) === true) {
                $this->removeByString($className);
            } else {
                throw new Exception('Invalid class name type');
            }
        }

        return $this;
    }

    /**
     * Helper which takes a string of one or
     * multiple classes to be removed from the list
     *
     * @param  string $className
     */
    protected function removeByString(string $className)
    {
        // prepare the class name
        $className = strtolower(trim($className));

        // are there still any spaces in the class name?
        if (strstr($className, ' ') !== false) {
            $names = explode(' ', $className);
            $this->remove(...$names);
        } elseif (empty($className) === false) {
            $this->removeFromList($className);
        }
    }

    protected function removeFromList(string $className)
    {
        // convert all class names to lowercase to
        // find better matches for passed class names
        $haystack = array_map('strtolower', $this->classNames);
        $key      = array_search($className, $haystack);

        if ($key !== false) {
            // remove the class
            array_splice($this->classNames, $key, 1);
        }
    }

    /**
     * Adds a className to the list if it does not
     * exist yet. Otherwise the className will be
     * removed
     *
     * @param  string    $className
     * @return ClassList
     */
    public function toggle(string $className): self
    {
        if ($this->contains($className) === true) {
            $this->remove($className);
        } else {
            $this->add($className);
        }

        return $this;
    }

    /**
     * Converts the class list to a plain array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->classNames;
    }

    /**
     * Converts the ClassList to a string
     * by concatenating all registered class names
     *
     * @return string
     */
    public function toString(): string
    {
        return implode(' ', $this->classNames);
    }

    /**
     * Magic string converter
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
