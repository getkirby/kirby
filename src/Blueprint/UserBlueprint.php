<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for users.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserBlueprint extends Blueprint
{
	/** @var \Kirby\Cms\User */
	protected ModelWithContent $model;

	/**
	 * UserBlueprint constructor.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(array $props)
	{
		parent::__construct($props);

		// normalize all available page options
		$this->props['options'] = $this->normalizeOptions(
			$this->props['options'] ?? true,
			// defaults
			[
				'access'         => null,
				'create'         => null,
				'changeEmail'    => null,
				'changeLanguage' => null,
				'changeName'     => null,
				'changePassword' => null,
				'changeRole'     => null,
				'delete'         => null,
				'list'           => null,
				'update'         => null,
			]
		);
	}

	/**
	 * Returns the role description
	 * @since 6.0.0
	 */
	public function description(): string|null
	{
		return $this->i18n($this->props['description'] ?? null);
	}
}
