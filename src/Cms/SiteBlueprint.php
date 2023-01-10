<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle the blueprint for the site.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class SiteBlueprint extends Blueprint
{
	/**
	 * Creates a new page blueprint object
	 * with the given props
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		// normalize all available page options
		$this->props['options'] = $this->normalizeOptions(
			$this->props['options'] ?? true,
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
	}

	/**
	 * Returns the preview settings
	 * The preview setting controls the "Open"
	 * button in the panel and redirects it to a
	 * different URL if necessary.
	 *
	 * @return string|bool
	 */
	public function preview()
	{
		$preview = $this->props['options']['preview'] ?? true;

		if (is_string($preview) === true) {
			return $this->model->toString($preview);
		}

		return $preview;
	}
}
