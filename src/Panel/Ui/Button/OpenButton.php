<?php

namespace Kirby\Panel\Ui\Button;

/**
 * Open view button
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class OpenButton extends ViewButton
{
	public function __construct(
		public string|null $link,
		public string|null $target = '_blank'
	) {
		parent::__construct(
			class: 'k-open-view-button',
			icon: 'open',
			link: $link,
			target: $target,
			title: $this->i18n('open')
		);
	}
}
