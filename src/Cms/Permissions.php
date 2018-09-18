<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * Handles permission definition in each user
 * blueprint and wraps a couple useful methods
 * around it to check for available permissions.
 */
class Permissions
{
    protected $actions = [
        'access' => [
            'panel' => true,
            'users' => true,
            'site'  => true
        ],
        'files' => [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'replace'    => true,
            'update'     => true
        ],
        'languages' => [
            'create' => true,
            'delete' => true
        ],
        'pages' => [
            'changeSlug'     => true,
            'changeStatus'   => true,
            'changeTemplate' => true,
            'changeTitle'    => true,
            'create'         => true,
            'delete'         => true,
            'preview'        => true,
            'sort'           => true,
            'update'         => true
        ],
        'site' => [
            'changeTitle' => true,
            'update'      => true
        ],
        'users' => [
            'changeEmail'    => true,
            'changeLanguage' => true,
            'changeName'     => true,
            'changePassword' => true,
            'changeRole'     => true,
            'create'         => true,
            'delete'         => true,
            'update'         => true
        ],
        'user' => [
            'changeEmail'    => true,
            'changeLanguage' => true,
            'changeName'     => true,
            'changePassword' => true,
            'changeRole'     => true,
            'delete'         => true,
            'update'         => true
        ]
    ];

    public function __construct($settings = [])
    {
        if (is_bool($settings) === true) {
            return $this->setAll($settings);
        }

        if (is_array($settings) === true) {
            return $this->setCategories($settings);
        }
    }

    public function for(string $category = null, string $action = null)
    {
        if ($action === null) {
            if ($this->hasCategory($category) === false) {
                return false;
            }

            return $this->actions[$category];
        }

        if ($this->hasAction($category, $action) === false) {
            return false;
        }

        return $this->actions[$category][$action];
    }

    protected function hasAction(string $category, string $action)
    {
        return $this->hasCategory($category) === true && array_key_exists($action, $this->actions[$category]) === true;
    }

    protected function hasCategory(string $category)
    {
        return array_key_exists($category, $this->actions) === true;
    }

    protected function setAction(string $category, string $action, $setting)
    {
        // wildcard to overwrite the entire category
        if ($action === '*') {
            return $this->setCategory($category, $setting);
        }

        $this->actions[$category][$action] = $setting;

        return $this;
    }

    protected function setAll(bool $setting)
    {
        foreach ($this->actions as $categoryName => $actions) {
            $this->setCategory($categoryName, $setting);
        }

        return $this;
    }

    protected function setCategories(array $settings)
    {
        foreach ($settings as $categoryName => $categoryActions) {
            if (is_bool($categoryActions) === true) {
                $this->setCategory($categoryName, $categoryActions);
            }

            if (is_array($categoryActions) === true) {
                foreach ($categoryActions as $actionName => $actionSetting) {
                    $this->setAction($categoryName, $actionName, $actionSetting);
                }
            }
        }

        return $this;
    }

    protected function setCategory(string $category, bool $setting)
    {
        if ($this->hasCategory($category) === false) {
            throw new InvalidArgumentException('Invalid permissions category');
        }

        foreach ($this->actions[$category] as $actionName => $actionSetting) {
            $this->actions[$category][$actionName] = $setting;
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->actions;
    }
}
