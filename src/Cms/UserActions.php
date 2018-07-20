<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
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
            if ($user->exists() === false) {
                return $user->clone([
                    'email' => $email
                ]);
            }

            Dir::remove($user->mediaRoot());

            $oldRoot = $user->root();
            $newRoot = dirname($user->root()) . '/' . $email;

            if (is_dir($newRoot) === true) {
                throw new DuplicateException([
                    'key'  => 'user.duplicate',
                    'data' => ['email' => $email]
                ]);
            }

            if (Dir::move($oldRoot, $newRoot) !== true) {
                throw new LogicException('The user directory for "' . $email . '" could not be moved');
            }

            return $user->clone([
                'email' => $email,
            ]);
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
            return $user->clone(['language' => $language])->save();
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
            return $user->clone(['name' => $name])->save();
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
            return $user->clone(['password' => $user->hashPassword($password)])->save();
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
            return $user->clone(['role' => $role])->save();
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
    protected function commit(string $action, $arguments = [], Closure $callback)
    {
        $old = $this->hardcopy();

        $this->rules()->$action(...$arguments);
        $this->kirby()->trigger('user.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $this->kirby()->trigger('user.' . $action . ':after', $result, $old);
        return $result;
    }

    /**
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

        return $user->commit('create', [$user, $props], function ($user, $props) {

            // try to create the directory
            if (Dir::make($user->root()) !== true) {
                throw new LogicException('The user directory for "' . $user->email() . '" could not be created');
            }

            // create an empty storage file
            touch($user->root() . '/user.txt');

            // write the user data
            return $user->save();
        });
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
     * Stores the user object on disk
     *
     * @return self
     */
    public function save(): self
    {
        $content = $this->content()->toArray();

        // store main information in the content file
        $content['language'] = $this->language();
        $content['name']     = $this->name();
        $content['password'] = $this->hashPassword($this->password());
        $content['role']     = $this->role()->id();

        // remove the email. It's already stored in the directory
        unset($content['email']);

        Data::write($this->root() . '/user.txt', $content);

        return $this;
    }

    /**
     * Updates the user content
     *
     * @param array $input
     * @param boolean $validate
     * @return self
     */
    public function update(array $input = null, bool $validate = true): self
    {
        $form = Form::for($this, [
            'values' => $input
        ]);

        // validate the input
        if ($validate === true && $form->isInvalid() === true) {
            throw new InvalidArgumentException([
                'fallback' => 'Invalid form with errors',
                'details'  => $form->errors()
            ]);
        }

        return $this->commit('update', [$this, $form->values(), $form->strings()], function ($user, $values, $strings) {
            $content = $user
                ->content()
                ->update($strings)
                ->toArray();

            return $user->clone(['content' => $content])->save();
        });
    }
}
