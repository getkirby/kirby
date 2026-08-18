<?php

namespace Kirby\Panel\Controller;

use Kirby\Blueprint\Section;
use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Panel\Area;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
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
				section: static::findSection(Find::parent($model), $filename),
				path: $section
			);
		}

		// for file section dialogs
		return new static(
			section: static::findSection(Find::file($model, $filename), $section),
			path: $path
		);
	}

	/**
	 * @throws NotFoundException If the section cannot be found
	 */
	protected static function findSection(
		ModelWithContent $model,
		string $name
	): Section {
		$section = $model->blueprint()->section($name);

		if ($section === null) {
			throw new NotFoundException(
				message: 'The section "' . $name . '" could not be found'
			);
		}

		return $section;
	}

	public function load(): mixed
	{
		return Router::execute($this->path, 'GET', $this->routes());
	}

	public function submit(): mixed
	{
		return Router::execute($this->path, 'POST', $this->routes());
	}
}
