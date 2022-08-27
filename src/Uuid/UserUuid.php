<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\User;

/**
 * Uuid for \Kirby\Cms\User
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
	 * Pretends to fill cache - we don't need it in cache
	 */
	public function populate(): bool
	{
		return true;
	}

	/**
	 * Returns the user object
	 */
	public function resolve(bool $lazy = false): User
	{
		return $this->model ??= App::instance()->user($this->id());
	}
}
