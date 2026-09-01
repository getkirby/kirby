<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\Html;

/**
 * Renders the `(email: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class EmailTag extends LinkTag
{
	public function __construct(
		public string|null $class = null,
		public string|null $rel = null,
		public string|null $text = null,
		public string|null $title = null
	) {
	}

	public function render(): string
	{
		return Html::email($this->value, $this->text, [
			'class' => $this->class,
			'rel'   => $this->rel,
			'title' => $this->title,
		]);
	}
}
