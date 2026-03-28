<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Site;

/**
 * UUID for \Kirby\Cms\Site
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 */
class SiteUuid extends Uuid
{
	protected const string TYPE = 'site';

	/**
	 * @var \Kirby\Cms\Site|null
	 */
	public Identifiable|null $model = null;

	/*
	 * Returns empty string since
	 * site doesn't really need an ID
	 */
	public function id(): string
	{
		return '';
	}

	/**
	 * Generator for the one and only site object
	 *
	 * @return \Generator|\Kirby\Cms\Site[]
	 */
	public static function index(): Generator
	{
		yield App::instance()->site();
	}

	/**
	 * Returns the site object
	 */
	public function model(bool $lazy = false): Site
	{
		return $this->model ??= App::instance()->site();
	}

	/**
	 * Pretends to fill cache - we don't need it in cache
	 */
	public function populate(bool $force = false): bool
	{
		return true;
	}

	/**
	 * Returns empty string since
	 * site doesn't really need an ID
	 */
	public static function retrieveId(Identifiable $model): string
	{
		return '';
	}

	/**
	 * Returns the full UUID string including scheme
	 */
	public function toString(): string
	{
		return 'site://';
	}
}
