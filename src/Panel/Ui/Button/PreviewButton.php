<?php

namespace Kirby\Panel\Ui\Button;

/**
 * Preview view button
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class PreviewButton extends ViewButton
{
	public function __construct(
		public string|null $link
	) {
		parent::__construct(
			class: 'k-preview-view-button',
			icon: 'window',
			link: $link,
			title: $this->i18n('preview')
		);
	}
}
