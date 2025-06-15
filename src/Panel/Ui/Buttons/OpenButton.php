<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Toolkit\I18n;

/**
 * Open view button
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
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
			title: I18n::translate('open')
		);
	}
}
