<?php

namespace Kirby\Http;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Http Acceptance negotiation
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Acceptance
{

    /**
     * List of all items in the acceptance chain
     *
     * @var array
     */
    protected $items = [];

    /**
     * Creates a new acceptance object from a string
     *
     * @param string $input
     */
    public function __construct(string $input)
    {
        foreach ($this->parse($input) as $quality => $values) {
            foreach ($values as $value) {
                $this->items[] = $this->item($value, floatval($quality));
            }
        }
    }

    /**
     * Parses the acceptance string and extracts all items
     * with their values and qualities
     *
     * @param  string $input
     * @return array
     */
    protected function parse(string $input): array
    {
        $items = [];

        // check each type in the Accept header
        foreach (Str::split($input, ',') as $item) {
            $parts   = Str::split($item, ';');
            $value   = A::first($parts); // $parts now only contains params
            $quality = 1;

            // check for the q param ("quality" of the type)
            foreach ($parts as $param) {
                $param = Str::split($param, '=');
                if (A::get($param, 0) === 'q' && !empty($param[1])) {
                    $quality = $param[1];
                }
            }

            $items[$quality][] = $value;
        }

        // sort items by quality
        krsort($items);

        return $items;
    }

    /**
     * Creates the data array for a single item
     *
     * @param  string $value
     * @param  float  $quality
     * @return array
     */
    public function item(string $value, float $quality): array
    {
        return [
            'value'   => $value,
            'quality' => $quality
        ];
    }

    /**
     * Returns the list of all items in the chain
     *
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Returns the information about the preferred
     * item in the acceptance chain. Can also be
     * used to return any particular value of the
     * information array by key.
     *
     * @param  string|null $key
     * @return mixed
     */
    public function info(string $key = null)
    {
        if ($key === null) {
            return $this->items[0] ?? [];
        }

        return $this->info()[$key] ?? null;
    }

    /**
     * Returns the value of the preferred
     * item in the acceptance chain
     *
     * @return string
     */
    public function value(): string
    {
        return $this->info('value');
    }

    /**
     * Returns the quality of the preferred
     * item in the acceptance chain
     *
     * @return float
     */
    public function quality(): float
    {
        return $this->info('quality');
    }

    /**
     * Matches item information against a given pattern
     * This is used in the `has` and `is` methods to
     * match particular items in the chain
     *
     * @param  array  $item
     * @param  string $pattern
     * @return boolean
     */
    protected function match(array $item, string $pattern): bool
    {
        return $item['value'] === $pattern;
    }

    /**
     * Checks if the perferred item in the chain
     * matches the given value
     *
     * @param  string  $value
     * @return boolean
     */
    public function is(string $value): bool
    {
        return $this->match($this->info(), $value);
    }

    /**
     * Checks if the acceptance chain includes the
     * given value.
     *
     * @param  string  $value
     * @return boolean
     */
    public function has(string $value): bool
    {
        foreach ($this->items as $item) {
            if ($this->match($item, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the info array of the preferred item
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->info();
    }

    /**
     * Returns the value of the preferred item
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value();
    }

    /**
     * Converts the Acceptance object to a string
     * by returning the value of the preferred item
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
