<?php

namespace Kirby\Panel\Ui\View;

use Kirby\Panel\Ui\View;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
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

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'error'  => $this->message,
			'layout' => $this->access ? 'inside' : 'outside'
		];
	}

	#[Override]
	public function render(): array|null
	{
		return [
			...parent::render(),
			'error' => $this->message
		];
	}
}
