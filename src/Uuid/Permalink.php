<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

/**
 * Permalink for \Kirby\Cms\Page or \Kirby\Cms\File
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Permalink
{
	public function __construct(
		public FileUuid|PageUuid $uuid
	) {
	}

	public static function from(string $uuid): static|null
	{
		if ($uuid = Uuid::for($uuid)) {
			return new static($uuid);
		}

		return null;
	}

	public function model(bool $lazy = false): Page|File|null
	{
		return $this->uuid->model($lazy);
	}

	public static function parse(string $url): static|null
	{
		if ($path = Str::after($url, '/@/')) {
			$path = explode('/', $path);

			if ($uuid = Uuid::for($path[0] . '://' . $path[1])) {
				return new static($uuid);
			}
		}

		return null;
	}

	public function url(): string
	{
		// make sure UUID is cached because the permalink
		// route only looks up UUIDs from cache
		$this->uuid->populate();

		$kirby = App::instance();
		$url   = $kirby->url();

		if ($language = $kirby->language('current')) {
			$url = $language->url();
		}

		return $url . '/@/' . $this->uuid->type() . '/' . $this->uuid->id();
	}

	public function uuid(): FileUuid|PageUuid
	{
		return $this->uuid;
	}

	public function __toString(): string
	{
		return $this->url();
	}
}
