<?php

namespace Kirby\Cms;

class BlueprintObject extends Object
{

    protected static $mixins = [];

    public function __construct(array $props = [])
    {
        parent::__construct($this->extend($props), $this->schema());
    }

    public function __debuginfo(): array
    {
        return $this->toArray();
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

    protected function schema(): array
    {
        return [];
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['collection']);
        ksort($array);

        return $array;
    }

    public function toLayout()
    {
        return $this->toArray();
    }

}
