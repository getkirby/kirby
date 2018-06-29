<?php

namespace Kirby\Cms;

use Kirby\Toolkit\V;

trait UserActions
{

    /**
     * Changes the user email address
     *
     * @param string $email
     * @return self
     */
    public function changeEmail(string $email): self
    {
        return $this->commit('changeEmail', $email);
    }

    /**
     * Changes the user language
     *
     * @param string $language
     * @return self
     */
    public function changeLanguage(string $language): self
    {
        return $this->commit('changeLanguage', $language);
    }

    /**
     * Changes the screen name of the user
     *
     * @param string $name
     * @return self
     */
    public function changeName(string $name): self
    {
        return $this->commit('changeName', $name);
    }

    /**
     * Changes the user password
     *
     * @param string $password
     * @return self
     */
    public function changePassword(string $password): self
    {
        $this->rules()->changePassword($this, $password);
        $this->kirby()->trigger('user.changePassword:before', $this);

        // hash password after checking rules
        $password = $this->hashPassword($password);

        // store the new password
        $result = $this->store()->changePassword($password);

        $this->kirby()->trigger('user.changePassword:after', $result, $this);
        return $result;
    }

    /**
     * Changes the user role
     *
     * @param string $role
     * @return self
     */
    public function changeRole(string $role): self
    {
        return $this->commit('changeRole', $role);
    }

    /**
     * Commits a user action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the store action
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param mixed ...$arguments
     * @return mixed
     */
    protected function commit(string $action, ...$arguments)
    {
        $old = $this->hardcopy();

        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('user.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
        $this->kirby()->trigger('user.' . $action . ':after', $result, $old);
        return $result;
    }

    /**
     * @param array $input
     * @return self
     */
    public static function create(array $props = null): self
    {
        $userProps = $props;

        // hash the password before creating the user
        if (isset($userProps['password']) === true) {
            $userProps['password'] = static::hashPassword($userProps['password']);
        }

        $user = new static($userProps);
        $user->rules()->create($user, $props);
        $user->kirby()->trigger('user.create:before', $userProps);
        $result = $user->store()->create($user);
        $user->kirby()->trigger('user.create:after', $result);
        return $result;
    }

    /**
     * Deletes the user
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->commit('delete');
    }
}
