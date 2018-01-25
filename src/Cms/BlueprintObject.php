<?php

namespace Kirby\Cms;

use Exception;

class BlueprintObject extends Model
{

    use HasI18n;

    protected static $mixins = [];

    public function __construct(array $props = [])
    {
        parent::__construct($this->extend($props));
    }

    protected function extend($props)
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

    public static function mixins(array $mixins = null): array
    {
        if ($mixins === null) {
            return static::$mixins;
        }

        return static::$mixins = $mixins;
    }

    public function toLayout()
    {
        return $this->toArray();
    }

}
