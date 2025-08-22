<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Site;
use Override;

/**
 * UUID for \Kirby\Cms\Site
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
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
	#[Override]
	public function id(): string
	{
		return '';
	}

	/**
	 * Generator for the one and only site object
	 *
	 * @return \Generator|\Kirby\Cms\Site[]
	 */
	#[Override]
	public static function index(): Generator
	{
		yield App::instance()->site();
	}

	/**
	 * Returns the site object
	 */
	#[Override]
	public function model(bool $lazy = false): Site
	{
		return $this->model ??= App::instance()->site();
	}

	/**
	 * Pretends to fill cache - we don't need it in cache
	 */
	#[Override]
	public function populate(bool $force = false): bool
	{
		return true;
	}

	/**
	 * Returns empty string since
	 * site doesn't really need an ID
	 */
	#[Override]
	public static function retrieveId(Identifiable $model): string
	{
		return '';
	}

	/**
	 * Returns the full UUID string including scheme
	 */
	#[Override]
	public function toString(): string
	{
		return 'site://';
	}
}
