<?php

namespace Kirby\Blueprint;

/**
 * Extension of the basic blueprint class
 * to handle the blueprint for the site.
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SiteBlueprint extends Blueprint
{
	protected function normalizeProps(array $props): array
	{
		$props = parent::normalizeProps($props);

		// normalize all available page options
		$props['options'] = OptionsProps::normalize(
			$props['options'] ?? true,
			// defaults
			[
				'changeTitle' => null,
				'update'      => null,
			],
			// aliases
			[
				'title' => 'changeTitle',
			]
		);

		return $props;
	}

	/**
	 * Returns the preview settings
	 * The preview setting controls the "Open"
	 * button in the panel and redirects it to a
	 * different URL if necessary.
	 */
	public function preview(): string|bool
	{
		$preview = $this->prop('options')['preview'] ?? true;

		if (is_string($preview) === true) {
			return $this->model->toString($preview);
		}

		return $this->model->permissions()->can('preview', true);
	}
}
