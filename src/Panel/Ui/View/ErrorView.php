<?php

namespace Kirby\Panel\Ui\View;

use Kirby\Panel\Ui\View;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @internal
 */
class ErrorView extends View
{
	public function __construct(
		public string $message,
		public bool $access = false,
		string|null $title = null
	) {
		parent::__construct(
			component: 'k-error-view',
			title:     $title,
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'error'  => $this->message,
			'layout' => $this->access ? 'inside' : 'outside'
		];
	}

	public function render(): array|null
	{
		return [
			...parent::render(),
			'error' => $this->message
		];
	}
}
