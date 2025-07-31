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
class Dialog extends Component
{
	public function __construct(
		string $component = 'k-dialog',
		public string|array|bool|null $cancelButton = null,
		string|null $class = null,
		public string|null $size = null,
		string|null $style = null,
		public string|array|bool|null $submitButton = null,
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component' => $component,
			'class'     => $class,
			'style'     => $style
		]);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'cancelButton' => $this->cancelButton,
			'size'         => $this->size,
			'submitButton' => $this->submitButton,
		];
	}
}
