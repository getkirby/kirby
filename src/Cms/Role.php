<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;

/**
 * Represents a User role with attached
 * permissions. Roles are defined by user blueprints.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Role extends Model
{
    protected $description;
    protected $name;
    protected $permissions;
    protected $title;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->name();
    }

    public static function admin(array $inject = [])
    {
        try {
            return static::load('admin');
        } catch (Exception $e) {
            return static::factory(static::defaults()['admin'], $inject);
        }
    }

    protected static function defaults(): array
    {
        return [
            'admin' => [
                'name'        => 'admin',
                'description' => I18n::translate('role.admin.description'),
                'title'       => I18n::translate('role.admin.title'),
                'permissions' => true,
            ],
            'nobody' => [
                'name'        => 'nobody',
                'description' => I18n::translate('role.nobody.description'),
                'title'       => I18n::translate('role.nobody.title'),
                'permissions' => false,
            ]
        ];
    }

    public function description()
    {
        return $this->description;
    }

    /**
     * @param array $props
     * @param array $inject
     * @return self
     */
    public static function factory(array $props, array $inject = [])
    {
        return new static($props + $inject);
    }

    public function id(): string
    {
        return $this->name();
    }

    public function isAdmin(): bool
    {
        return $this->name() === 'admin';
    }

    public function isNobody(): bool
    {
        return $this->name() === 'nobody';
    }

    /**
     * @param string $file
     * @param array $inject
     * @return self
     */
    public static function load(string $file, array $inject = [])
    {
        $data = Data::read($file);
        $data['name'] = F::name($file);

        return static::factory($data, $inject);
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param array $inject
     * @return self
     */
    public static function nobody(array $inject = [])
    {
        try {
            return static::load('nobody');
        } catch (Exception $e) {
            return static::factory(static::defaults()['nobody'], $inject);
        }
    }

    /**
     * @return \Kirby\Cms\Permissions
     */
    public function permissions()
    {
        return $this->permissions;
    }

    /**
     * @param [type] $description
     * @return self
     */
    protected function setDescription($description = null)
    {
        $this->description = I18n::translate($description, $description);
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    protected function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param [type] $permissions
     * @return self
     */
    protected function setPermissions($permissions = null)
    {
        $this->permissions = new Permissions($permissions);
        return $this;
    }

    /**
     * @param [type] $title
     * @return self
     */
    protected function setTitle($title = null)
    {
        $this->title = I18n::translate($title, $title);
        return $this;
    }

    public function title(): string
    {
        return $this->title = $this->title ?? ucfirst($this->name());
    }

    /**
     * Converts the most important role
     * properties to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description(),
            'id'          => $this->id(),
            'name'        => $this->name(),
            'permissions' => $this->permissions()->toArray(),
            'title'       => $this->title(),
        ];
    }
}
