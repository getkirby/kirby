<?php

namespace Kirby\Text\Tag;

use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Escape;

/**
 * Renders the `(date: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class DateTag extends KirbyTag
{
	public function __construct(
		public string|null $expiry = null
	) {
	}

	public function render(): string
	{
		if (strtolower($this->value) === 'year') {
			// make sure cached pages reset the year at New Year,
			// unless a custom expiry point has been requested
			$this->kirby()->response()->expires(
				$this->expiry ?? 'first day of January next year'
			);

			return date('Y');
		}

		// let authors control the cache expiry for custom formats,
		// e.g. `(date: Y-m-d expiry: tomorrow)`
		if ($this->expiry !== null) {
			$this->kirby()->response()->expires($this->expiry);
		}

		// escape the formatted date to prevent injecting HTML
		// through special characters in the tag value
		return Escape::html(date($this->value));
	}
}
