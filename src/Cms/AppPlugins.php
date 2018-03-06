<?php

namespace Kirby\Cms;

use Kirby\Form\Field;
use Kirby\Util\Dir;

trait AppPlugins
{

    protected function plugins()
    {
        $root  = $this->root('plugins');
        $kirby = $this;

        foreach (Dir::read($root) as $dirname) {

            if (is_dir($root . '/' . $dirname) === false) {
                continue;
            }

            $entry = $root . '/' . $dirname . '/' . $dirname . '.php';

            if (file_exists($entry) === false) {
                continue;
            }

            include_once $entry;

        }

        $this->registerContentFieldMethods();
        $this->registerFields();
        $this->registerHooks();
        $this->registerPageModels();

    }

    protected function registerContentFieldMethods()
    {
        $default = include static::$root . '/extensions/methods.php';
        $plugins = $this->registry->get('fieldMethod');

        // field methods
        ContentField::$methods = array_merge($default, $plugins);
    }

    protected function registerFields()
    {
        Field::$types = $this->get('field');
    }

    protected function registerHooks()
    {
        $this->hooks()->registerAll($this->get('hook'));
    }

    protected function registerPageModels()
    {
        // page models
        Page::$models = $this->get('pageModel');
    }

}
