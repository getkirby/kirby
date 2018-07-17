<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\F;

/**
 * Represents a User role with attached
 * permissions. Roles are defined by user blueprints.
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
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
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

    protected static function defaults()
    {
        return [
            'admin' => [
                'description' => 'The admin has all rights',
                'name'        => 'admin',
                'title'       => 'Admin',
                'permissions' => true,
            ],
            'nobody' => [
                'description' => 'This is a fallback role without any permissions',
                'name'        => 'nobody',
                'title'       => 'Nobody',
                'permissions' => false,
            ]
        ];
    }

    public function description()
    {
        return $this->description;
    }

    public static function factory(array $props, array $inject = []): self
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

    public static function load(string $file, array $inject = []): self
    {
        $name = F::name($file);

        try {
            $data = Data::read($file);
            $data['name'] = $name;

            return static::factory($data, $inject);
        } catch (Exception $e) {
            throw new NotFoundException(sprintf('The role "%s" does not exist', $name));
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public static function nobody(array $inject = [])
    {
        try {
            return static::load('nobody');
        } catch (Exception $e) {
            return static::factory(static::defaults()['nobody'], $inject);
        }
    }

    public function permissions(): Permissions
    {
        return $this->permissions;
    }

    protected function setDescription(string $description = null): self
    {
        $this->description = $description;
        return $this;
    }

    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    protected function setPermissions($permissions = null): self
    {
        $this->permissions = new Permissions($permissions);
        return $this;
    }

    protected function setTitle($title = null): self
    {
        $this->title = $title;
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
