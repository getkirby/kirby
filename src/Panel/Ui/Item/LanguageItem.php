<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Language;
use Kirby\Panel\Ui\Item;
use Kirby\Toolkit\Escape;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LanguageItem extends Item
{
	public function __construct(
		protected Language $language
	) {
		parent::__construct(
			info: Escape::html($language->code()),
			text: Escape::html($language->name()),
			image: [
				'back'  => 'black',
				'color' => 'gray',
				'icon'  => 'translate',
			]
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'default'   => $this->language->isDefault(),
			'deletable' => $this->language->isDeletable(),
			'id'        => $this->language->code(),
		];
	}
}
