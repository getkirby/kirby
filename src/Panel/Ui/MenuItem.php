<?php

namespace Kirby\Panel\Ui;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class MenuItem extends Button
{
	public function __construct(
		public string $id,
		string|bool|null $current = false,
		bool $disabled = false,
		string|null $dialog = null,
		string|null $drawer = null,
		string|null $icon = null,
		string|null $link = null,
		string|null $target = null,
		string|array|null $text = null,
		string|array|null $title = null
	) {
		// unset the link (which is always added by default to an area)
		// if a dialog or drawer should be opened instead
		if ($dialog !== null || $drawer !== null) {
			$link = null;
		}

		parent::__construct(
			current: $current,
			disabled: $disabled,
			dialog: $dialog,
			drawer: $drawer,
			icon: $icon,
			link: $link,
			target: $target,
			text: $text,
			title: $title,
		);
	}

	/**
	 * Checks if the menu item is a valid alternative
	 * for the home page, in case the original cannot be accessed
	 */
	public function isAlternative(): bool
	{
		// skip disabled items
		if ($this->disabled === true) {
			return false;
		}

		// skip items without a link
		if ($this->link === null) {
			return false;
		}

		// skip the logout button
		if ($this->link === 'logout') {
			return false;
		}

		return true;
	}
}
