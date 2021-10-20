<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * Handles permission definition in each user
 * blueprint and wraps a couple useful methods
 * around it to check for available permissions.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Permissions
{
    /**
     * @var array
     */
    public static $extendedActions = [];

    /**
     * @var array
     */
    protected $actions = [
        'access' => [
            'account'   => true,
            'languages' => true,
            'panel'     => true,
            'site'      => true,
            'system'    => true,
            'users'     => true,
        ],
        'files' => [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'read'       => true,
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
            'duplicate'      => true,
            'preview'        => true,
            'read'           => true,
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

    /**
     * Permissions constructor
     *
     * @param array $settings
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function __construct($settings = [])
    {
        // dynamically register the extended actions
        foreach (static::$extendedActions as $key => $actions) {
            if (isset($this->actions[$key]) === true) {
                throw new InvalidArgumentException('The action ' . $key . ' is already a core action');
            }

            $this->actions[$key] = $actions;
        }

        if (is_array($settings) === true) {
            return $this->setCategories($settings);
        }

        if (is_bool($settings) === true) {
            return $this->setAll($settings);
        }
    }

    /**
     * @param string|null $category
     * @param string|null $action
     * @return bool
     */
    public function for(string $category = null, string $action = null): bool
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

    /**
     * @param string $category
     * @param string $action
     * @return bool
     */
    protected function hasAction(string $category, string $action): bool
    {
        return $this->hasCategory($category) === true && array_key_exists($action, $this->actions[$category]) === true;
    }

    /**
     * @param string $category
     * @return bool
     */
    protected function hasCategory(string $category): bool
    {
        return array_key_exists($category, $this->actions) === true;
    }

    /**
     * @param string $category
     * @param string $action
     * @param $setting
     * @return $this
     */
    protected function setAction(string $category, string $action, $setting)
    {
        // deprecated fallback for the settings/system view
        // TODO: remove in 3.7
        if ($category === 'access' && $action === 'settings') {
            $action = 'system';
        }

        // wildcard to overwrite the entire category
        if ($action === '*') {
            return $this->setCategory($category, $setting);
        }

        $this->actions[$category][$action] = $setting;

        return $this;
    }

    /**
     * @param bool $setting
     * @return $this
     */
    protected function setAll(bool $setting)
    {
        foreach ($this->actions as $categoryName => $actions) {
            $this->setCategory($categoryName, $setting);
        }

        return $this;
    }

    /**
     * @param array $settings
     * @return $this
     */
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

    /**
     * @param string $category
     * @param bool $setting
     * @return $this
     * @throws \Kirby\Exception\InvalidArgumentException
     */
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->actions;
    }
}
