<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\Html;
use Kirby\Text\KirbyTag;

/**
 * Renders the `(gist: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class GistTag extends KirbyTag
{
	public function __construct(
		public string|null $file = null
	) {
	}

	public function render(): string
	{
		return Html::gist($this->value, $this->file);
	}
}
