<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Toolkit\I18n;

/**
 * Preview dropdown view button
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PreviewDropdownButton extends ViewButton
{
	public function __construct(
		public string $open,
		public string|null $preview,
		public string|null $copy
	) {
		parent::__construct(
			class: 'k-preview-dropdown-view-button',
			icon: 'open',
			options: $this->options(),
			title: I18n::translate('open')
		);
	}

	public function options(): array
	{
		return [
			[
				'text'   => I18n::translate('open'),
				'icon'   => 'open',
				'link'   => $this->open,
				'target' => '_blank'
			],
			[
				'text' => I18n::translate('preview'),
				'icon' => 'window',
				'link' => $this->preview,
			],
			'-',
			[
				'text' => I18n::translate('copy.url'),
				'icon' => 'copy',
				'click' => [
					'global'  => 'clipboard.write',
					'payload' => $this->copy
				]
			]
		];
	}
}
