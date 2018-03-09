<?php

namespace Kirby\Cms;

use Exception;
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
        $this->rules()->changeEmail($this, $email);

        return $this->store()->changeEmail($email);
    }

    /**
     * Changes the user language
     *
     * @param string $language
     * @return self
     */
    public function changeLanguage(string $language): self
    {
        $this->rules()->changeLanguage($this, $language);

        return $this->store()->changeLanguage($language);
    }

    /**
     * Changes the screen name of the user
     *
     * @param string $name
     * @return self
     */
    public function changeName(string $name): self
    {
        return $this->store()->changeName($name);
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

        // hash password after checking rules
        $password = $this->hashPassword($password);

        return $this->store()->changePassword($password);
    }

    /**
     * Changes the user role
     *
     * @param string $role
     * @return self
     */
    public function changeRole(string $role): self
    {
        $this->rules()->changeRole($this, $role);

        return $this->store()->changeRole($role);
    }

    /**
     * @param array $input
     * @return self
     */
    public function create(array $input = null): self
    {
        // stop if the user already exists
        if ($this->exists() === true) {
            throw new Exception('The user already exists');
        }

        $form = Form::for($this, [
            'values' => $input
        ]);

        // validate the input
        $form->isValid();

        // get the data values array
        $values = $form->values();

        // validate those values additionally with the model rules
        $this->rules()->create($this, $values, $form);

        // store and pass the form as second param
        // to make use of the Form::stringValues() method
        // if necessary
        return $this->store()->create($values, $form);
    }

    /**
     * Deletes the user
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->delete($this);

        return $this->store()->delete();
    }

}
