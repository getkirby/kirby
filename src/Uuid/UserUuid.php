<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\User;

/**
 * UUID for $user
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 *
 * @extends Uuid<User>
 */
class UserUuid extends Uuid
{
	protected const string TYPE = 'user';

	/*
	 * Returns the user ID
	 * (we can rely in this case that the Uri was filled
	 * with the model ID on initiation)
	 */
	public function id(): string
	{
		/** @var string - is always set from User::id() at construction */
		return $this->uri->host();
	}

	/**
	 * Generator for all users
	 *
	 * @return Generator<string, User>
	 */
	public static function index(): Generator
	{
		yield from App::instance()->users();
	}

	/**
	 * Returns the user object
	 */
	public function model(bool $lazy = false): User|null
	{
		return $this->model ??= App::instance()->user($this->id());
	}

	/**
	 * Pretends to fill cache - we don't need it in cache
	 */
	public function populate(bool $force = false): bool
	{
		return true;
	}
}
