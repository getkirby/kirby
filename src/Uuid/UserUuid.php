<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\User;

/**
 * UUID for \Kirby\Cms\User
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserUuid extends Uuid
{
	protected const TYPE = 'user';

	/**
	 * @var \Kirby\Cms\User|null
	 */
	public Identifiable|null $model;

	/**
	 * Generator for all users
	 *
	 * @return \Generator|\Kirby\Cms\User[]
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
	public function populate(): bool
	{
		return true;
	}
}
