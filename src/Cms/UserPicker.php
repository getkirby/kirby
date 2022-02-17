<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The UserPicker class helps to
 * fetch the right files for the API calls
 * for the user picker component in the panel.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserPicker extends Picker
{
    /**
     * Extends the basic defaults
     *
     * @return array
     */
    public function defaults(): array
    {
        $defaults = parent::defaults();
        $defaults['text'] = '{{ user.username }}';

        return $defaults;
    }

    /**
     * Search all users for the picker
     *
     * @return \Kirby\Cms\Users|null
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function items()
    {
        $model = $this->options['model'];

        // find the right default query
        if (empty($this->options['query']) === false) {
            $query = $this->options['query'];
        } elseif (is_a($model, 'Kirby\Cms\User') === true) {
            $query = 'user.siblings';
        } else {
            $query = 'kirby.users';
        }

        // fetch all users for the picker
        $users = $model->query($query);

        // catch invalid data
        if (is_a($users, 'Kirby\Cms\Users') === false) {
            throw new InvalidArgumentException('Your query must return a set of users');
        }

        // search
        $users = $this->search($users);

        // sort
        $users = $users->sort('username', 'asc');

        // paginate
        return $this->paginate($users);
    }
}
