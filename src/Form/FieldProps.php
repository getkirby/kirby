<?php

namespace Kirby\Form;

use Closure;
use Kirby\Util\I18n;

class FieldProps extends FieldDefinition
{

    protected $aliases = [
        'readonly' => 'disabled'
    ];

    /**
     * Default props for all fields
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'disabled' => false,
            'help'     => function ($value = null) {
                return I18n::translate($value, $value);
            },
            'label'    => function ($value = null) {
                return I18n::translate($value, $value) ?? ucfirst($this->type());
            },
            'name'     => function ($value = null) {
                return $value ?? $this->type();
            },
            'width'    => '1/1'
        ];
    }

    /**
     * Renames props based on alias array
     *
     * @param  array $props
     * @return array
     */
    public function resolveAliases(array $props): array
    {
        foreach ($this->aliases as $alias => $name) {
            if (isset($props[$alias]) === true) {
                $props[$name] = $props[$alias];
                unset($props[$alias]);
            }
        }

        return $props;
    }

    /**
     * Set a single prop with transforming the value via the prop
     * definition and/or the default value provided by the definition
     *
     * @param  string $name
     * @param  any    $value
     * @return any
     */
    public function setProp(string $name, $value)
    {
        $definition = $this->definition[$name] ?? null;

        if (is_a($definition, Closure::class) === true) {
            $this->definition[$name] = $definition->call($this->field, $value);
        } else {
            $this->definition[$name] = $value ?? $definition;
        }

        return $this->definition[$name];
    }

    /**
     * Sets multiple props at once
     *
     * @param  array $props
     * @return array
     */
    public function setProps(array $props): array
    {

        // Rename props according to aliases
        $props = $this->resolveAliases($props);

        // Set each prop and value
        foreach ($props as $name => $value) {
            $this->setProp($name, $value);
        }

        // Get defaults for all remaining closures as prop definition
        foreach ($this->definition as $name => $value) {
            if (is_a($value, Closure::class) === true) {
                $this->definition[$name] = $value->call($this->field, null);
            }
        }

        return $this->definition;
    }
}
