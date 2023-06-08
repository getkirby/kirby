<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for users.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserBlueprint extends Blueprint
{
	/**
	 * UserBlueprint constructor.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(array $props)
	{
		// normalize and translate the description
		$props['description'] = $this->i18n($props['description'] ?? null);

		// register the other props
		parent::__construct($props);

		// normalize all available page options
		$this->props['options'] = $this->normalizeOptions(
			$this->props['options'] ?? true,
			// defaults
			[
				'create'         => null,
				'changeEmail'    => null,
				'changeLanguage' => null,
				'changeName'     => null,
				'changePassword' => null,
				'changeRole'     => null,
				'delete'         => null,
				'update'         => null,
			]
		);
	}
}
