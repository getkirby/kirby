<?php

namespace Kirby\Cms;

class BlueprintObject extends Object
{
    protected static $mixins = [];

    public function __construct(array $props)
    {
        parent::__construct($this->extend($props), $this->schema());
    }

    public function schema(): array
    {
        return [];
    }

    public function extend($props)
    {
        if (isset($props['extends']) === false) {
            return $props;
        }

        if ($mixin = static::mixin($props['extends'])) {
            $props = array_replace_recursive($mixin, $props);
        }

        unset($props['extends']);

        return $props;
    }

    public static function mixin(string $path, array $mixin = null): array
    {
        if ($mixin === null) {
            return static::$mixins[$path] ?? null;
        }

        return static::$mixins[$path] = $mixin;
    }

    public function toArray(): array
    {
        return $this->props;
    }
}
