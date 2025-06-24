<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Toolkit\I18n;

/**
 * Preview view button
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
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
			title: I18n::translate('preview')
		);
	}
}
