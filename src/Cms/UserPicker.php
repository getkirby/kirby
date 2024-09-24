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
	 */
	public function defaults(): array
	{
		return [
			...parent::defaults(),
			'text' => '{{ user.username }}'
		];
	}

	/**
	 * Search all users for the picker
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function items(): Users|null
	{
		$model = $this->options['model'];

		// find the right default query
		$query = match (true) {
			empty($this->options['query']) === false
				=> $this->options['query'],
			$model instanceof User
				=> 'user.siblings',
			default
			=> 'kirby.users'
		};

		// fetch all users for the picker
		$users = $model->query($query);

		// catch invalid data
		if ($users instanceof Users === false) {
			throw new InvalidArgumentException(
				message: 'Your query must return a set of users'
			);
		}

		// search & sort
		$users = $this->search($users)->sort('username', 'asc');

		// paginate
		return $this->paginate($users);
	}
}
