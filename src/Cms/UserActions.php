<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentLogicException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
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
        return $this->commit('changeEmail', [$this, $email], function ($user, $email) {
            $user = $user->clone([
                'email' => $email
            ]);

            $user->updateCredentials([
                'email' => $email
            ]);

            return $user;
        });
    }

    /**
     * Changes the user language
     *
     * @param string $language
     * @return self
     */
    public function changeLanguage(string $language): self
    {
        return $this->commit('changeLanguage', [$this, $language], function ($user, $language) {
            $user = $user->clone([
                'language' => $language,
            ]);

            $user->updateCredentials([
                'language' => $language
            ]);

            return $user;
        });
    }

    /**
     * Changes the screen name of the user
     *
     * @param string $name
     * @return self
     */
    public function changeName(string $name): self
    {
        return $this->commit('changeName', [$this, $name], function ($user, $name) {
            $user = $user->clone([
                'name' => $name
            ]);

            $user->updateCredentials([
                'name' => $name
            ]);

            return $user;
        });
    }

    /**
     * Changes the user password
     *
     * @param string $password
     * @return self
     */
    public function changePassword(string $password): self
    {
        return $this->commit('changePassword', [$this, $password], function ($user, $password) {
            $user = $user->clone([
                'password' => $password = $user->hashPassword($password)
            ]);

            $user->writePassword($password);

            return $user;
        });
    }

    /**
     * Changes the user role
     *
     * @param string $role
     * @return self
     */
    public function changeRole(string $role): self
    {
        return $this->commit('changeRole', [$this, $role], function ($user, $role) {
            $user = $user->clone([
                'role' => $role,
            ]);

            $user->updateCredentials([
                'role' => $role
            ]);

            return $user;
        });
    }

    /**
     * Commits a user action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the action
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param array $arguments
     * @param Closure $callback
     * @return mixed
     */
    protected function commit(string $action, array $arguments = [], Closure $callback)
    {
        if ($this->isKirby() === true) {
            throw new PermissionException('The Kirby user cannot be changed');
        }

        $old = $this->hardcopy();

        $this->rules()->$action(...$arguments);
        $this->kirby()->trigger('user.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $this->kirby()->trigger('user.' . $action . ':after', $result, $old);
        $this->kirby()->cache('pages')->flush();
        return $result;
    }

    /**
     * Creates a new User from the given props and returns a new User object
     *
     * @param array $input
     * @return self
     */
    public static function create(array $props = null): self
    {
        $data = $props;

        if (isset($props['password']) === true) {
            $data['password'] = static::hashPassword($props['password']);
        }

        $user = new static($data);

        // create a form for the user
        $form = Form::for($user, [
            'values' => $props['content'] ?? []
        ]);

        // inject the content
        $user = $user->clone(['content' => $form->strings(true)]);

        // run the hook
        return $user->commit('create', [$user, $props], function ($user, $props) {
            $user->writeCredentials([
                'email'    => $user->email(),
                'language' => $user->language(),
                'name'     => $user->name()->value(),
                'role'     => $user->role()->id(),
            ]);

            $user->writePassword($user->password());

            // always create users in the default language
            if ($user->kirby()->multilang() === true) {
                $languageCode = $user->kirby()->defaultLanguage()->code();
            } else {
                $languageCode = null;
            }

            // write the user data
            return $user->save($user->content()->toArray(), $languageCode);
        });
    }

    /**
     * Returns a random user id
     *
     * @return string
     */
    public function createId(): string
    {
        $length = 8;
        $id     = Str::random($length);

        while ($this->kirby()->users()->has($id)) {
            $length++;
            $id = Str::random($length);
        }

        return $id;
    }

    /**
     * Deletes the user
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->commit('delete', [$this], function ($user) {
            if ($user->exists() === false) {
                return true;
            }

            // delete all public assets for this user
            Dir::remove($user->mediaRoot());

            // delete the user directory
            if (Dir::remove($user->root()) !== true) {
                throw new LogicException('The user directory for "' . $user->email() . '" could not be deleted');
            }

            return true;
        });
    }

    /**
     * Read the account information from disk
     *
     * @return array
     */
    protected function readCredentials(): array
    {
        if (file_exists($this->root() . '/index.php') === true) {
            $credentials = require $this->root() . '/index.php';

            return is_array($credentials) === false ? [] : $credentials;
        } else {
            return [];
        }
    }

    /**
     * Reads the user password from disk
     *
     * @return string|null
     */
    protected function readPassword(): ?string
    {
        return F::read($this->root() . '/.htpasswd');
    }

    /**
     * This always merges the existing credentials
     * with the given input.
     *
     * @param array $credentials
     * @return bool
     */
    protected function updateCredentials(array $credentials): bool
    {
        return $this->writeCredentials(array_merge($this->credentials(), $credentials));
    }

    /**
     * Writes the account information to disk
     *
     * @return boolean
     */
    protected function writeCredentials(array $credentials): bool
    {
        return Data::write($this->root() . '/index.php', $credentials);
    }

    /**
     * Writes the password to disk
     *
     * @param string $password
     * @return bool
     */
    protected function writePassword(string $password = null): bool
    {
        return F::write($this->root() . '/.htpasswd', $password);
    }
}
