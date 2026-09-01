<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

/**
 * Renders the `(link: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class LinkTag extends KirbyTag
{
	public function __construct(
		public string|null $class = null,
		public string|null $lang = null,
		public string|null $rel = null,
		public string|null $role = null,
		public string|null $target = null,
		public string|null $title = null,
		public string|null $text = null
	) {
	}

	protected function error(): string
	{
		// debug: visible inline error, no styles to avoid clashes
		if ($this->kirby()->option('debug', false) === true) {
			$error = 'The link "' . $this->value . '" cannot be found';

			if ($this->text !== null && $this->text !== '') {
				$error .= ' for the link text "' . $this->text . '"';
			}

			return Html::tag('span', '🚨 ' . $error, [
				'class' => Str::trim('kirby-broken-link ' . $this->class)
			]);
		}

		// otherwise drop the link, keep its text (if any)
		if ($this->text !== null && $this->text !== '') {
			return Html::tag('span', $this->text, [
				'class' => $this->class
			]);
		}

		return '';
	}

	public function render(): string
	{
		$url = $this->url();

		// broken link: handle inline instead of turning
		// the whole page into the error page
		if ($url === null) {
			return $this->error();
		}

		return Html::a($url, $this->text, [
			'rel'    => $this->rel,
			'class'  => $this->class,
			'role'   => $this->role,
			'title'  => $this->title,
			'target' => $this->target,
		]);
	}

	protected function url(): string|null
	{
		// keep $this->value as the original value (e.g. UUID) for errors
		$url = $this->value;

		if ($this->lang !== null && $this->lang !== '') {
			$url = Url::to($url, $this->lang);
		}

		// if the value is a UUID, resolve to the page/file model
		// and use its URL
		if (Uuid::is($url, ['page', 'file']) === true) {
			$url = Uuid::from($url)?->toUrl();
		}

		return $url;
	}
}
