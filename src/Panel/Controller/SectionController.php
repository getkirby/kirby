<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\Find;
use Kirby\Cms\Section;
use Kirby\Http\Router;
use Kirby\Panel\Area;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
trait SectionController
{
	public function __construct(
		public Section $section,
		public string|null $path = null
	) {
	}

	protected function area(): Area
	{
		return new Area(id: 'site');
	}

	public static function factory(
		string $model,
		string $filename,
		string $section,
		string|null $path = null
	) {
		// for page/user/site section dialogs
		if ($path === null) {
			return new static(
				section: Find::parent($model)->blueprint()->section($filename),
				path: $section
			);
		}

		// for file section dialogs
		return new static(
			section: Find::file($model, $filename)->blueprint()->section($section),
			path: $path
		);
	}

	#[Override]
	public function load(): mixed
	{
		return Router::execute($this->path, 'GET', $this->routes());
	}

	#[Override]
	public function submit(): mixed
	{
		return Router::execute($this->path, 'POST', $this->routes());
	}
}
