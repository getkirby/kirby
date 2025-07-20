<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Toolkit\A;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Areas
{
	protected array $areas;
	protected App $kirby;

	public function __construct(
		protected Panel $panel
	) {
		$this->kirby = $this->panel->kirby();
		$this->areas = $this->load();
	}

	/**
	 * Normalize a panel area
	 */
	public static function area(string $id, array $area): array
	{
		$area['id']                = $id;
		$area['label']           ??= $id;
		$area['breadcrumb']      ??= [];
		$area['breadcrumbLabel'] ??= $area['label'];
		$area['title']             = $area['label'];
		$area['menu']            ??= false;
		$area['link']            ??= $id;
		$area['search']          ??= null;

		return $area;
	}

	/**
	 * Collect all registered buttons from areas
	 */
	public function buttons(): array
	{
		return array_merge(...array_values(
			A::map(
				$this->areas,
				fn (array $area) => $area['buttons'] ?? []
			)
		));
	}

	public function load(): array
	{
		$system = $this->kirby->system();
		$user   = $this->kirby->user();
		$areas  = $this->kirby->load()->areas();

		// the system is not ready
		if (
			$system->isOk() === false ||
			$system->isInstalled() === false
		) {
			return [
				'installation' => static::area(
					'installation',
					$areas['installation']
				),
			];
		}

		// not yet authenticated
		if ($user === null) {
			return [
				'logout' => static::area('logout', $areas['logout']),
				// login area last because it defines a fallback route
				'login'  => static::area('login', $areas['login']),
			];
		}

		unset($areas['installation'], $areas['login']);

		// Disable the language area for single-language installations
		// This does not check for installed languages. Otherwise you'd
		// not be able to add the first language through the view
		if ($this->kirby->option('languages') !== true) {
			unset($areas['languages']);
		}

		$result = [];

		foreach ($areas as $id => $area) {
			$result[$id] = static::area($id, $area);
		}

		return $result;
	}

	public function toArray(): array
	{
		return $this->areas;
	}
}
