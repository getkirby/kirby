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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class UserPicker
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Creates a new UserPicker instance
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        // default params
        $defaults = [
            // image settings (ratio, cover, etc.)
            'image' => [],
            // query template for the page info field
            'info' => false,
            // number of pages displayed per pagination page
            'limit' => 20,
            // optional mapping function for the pages array
            'map' => null,
            // the reference model (user)
            'model' => site(),
            // current page when paginating
            'page' => 1,
            // a query string to fetch specific pages
            'query' => null,
            // query template for the user text field
            'text' => '{{ user.username }}'
        ];

        $this->options = array_merge($defaults, $params);
    }

    /**
     * Search all users for the picker
     *
     * @return \Kirby\Cms\Users|null
     */
    public function users()
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

        // sort users
        $users = $users->sortBy('username', 'asc');

        // paginate the result
        $users = $users->paginate([
            'limit' => $this->options['limit'],
            'page'  => $this->options['page']
        ]);

        return $users;
    }

    /**
     * Converts all given users to an associative
     * array that is already optimized for the
     * panel picker component.
     *
     * @param \Kirby\Cms\Users|null $users
     * @return array
     */
    public function usersToArray($users): array
    {
        if ($users === null) {
            return [];
        }

        $result = [];

        foreach ($users as $index => $user) {
            if (empty($this->options['map']) === false) {
                $result[] = $this->options['map']($user);
            } else {
                $result[] = $user->panelPickerData([
                    'image' => $this->options['image'],
                    'info'  => $this->options['info'],
                    'model' => $this->options['model'],
                    'text'  => $this->options['text'],
                ]);
            }
        }

        return $result;
    }

    /**
     * Return the most relevant pagination
     * info as array
     *
     * @param \Kirby\Cms\Pagination $pagination
     * @return array
     */
    public function paginationToArray(Pagination $pagination): array
    {
        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total()
        ];
    }

    /**
     * Returns an associative array
     * with all information for the picker.
     * This will be passed directly to the API.
     *
     * @return array
     */
    public function toArray(): array
    {
        $users = $this->users();

        return [
            'data'       => $this->usersToArray($users),
            'pagination' => $this->paginationToArray($users->pagination())
        ];
    }
}
