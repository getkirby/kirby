<?php

namespace Kirby\Cms;

use Kirby\Util\Dir;
use Kirby\Util\F;

class Roles extends Collection
{
    protected static $accept = Role::class;

    public static function factory(): self
    {
        // user blueprint root
        $app   = App::instance();
        $root  = $app->root('blueprints') . '/users';
        $roles = new static;

        // read all user blueprints
        foreach (Dir::read($root) as $filename) {
            $file = $root . '/' . $filename;
            $name = strtolower(F::name($filename));

            if (is_file($file) !== true) {
                continue;
            }

            if ($name === 'default') {
                continue;
            }

            $role = Role::factory($name);
            $roles->set($role->name(), $role);
        }

        return $roles;
    }

}
