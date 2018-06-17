<?php

namespace Kirby\Cms;

use Closure;

/**
 * The Ingredients class is the foundation for
 * $kirby->urls() and $kirby->roots() objects.
 * Those are configured in `kirby/config/urls.php`
 * and `kirby/config/roots.php`
 */
class Ingredients
{
    protected $ingredients;

    public function __construct(array $ingredients)
    {
        $this->ingredients = $ingredients;
    }

    public function __call(string $method, array $args = null)
    {
        return $this->ingredients[$method] ?? null;
    }

    public function __get(string $key)
    {
        return $this->ingredients[$key] ?? null;
    }

    public static function bake(array $ingredients): self
    {
        foreach ($ingredients as $name => $ingredient) {
            if (is_a($ingredient, Closure::class) === true) {
                $ingredients[$name] = $ingredient($ingredients);
            }
        }

        return new static($ingredients);
    }

    public function toArray(): array
    {
        return $this->ingredients;
    }
}
