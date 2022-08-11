<?php

namespace Kirby\Email;

/**
 * Representation of a an Email body
 * with a text and optional html version
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Body
{
	protected string $html;
	protected string $text;

	/**
	 * Email body constructor
	 */
	public function __construct(
		string|array|null $html = null,
		string|null $text = null
	) {
		// support deprecated passign props as array
		// TODO: add deprecation warning at some point
		if (is_array($html) === true) {
			$text ??= $html['text'] ?? null;
			$html   = $html['html'] ?? null;
		}

		$this->html = $html ?? '';
		$this->text = $text ?? '';
	}

	/**
	 * Clone the body instance and
	 * pass modified properties
	 */
	public function clone(
		array|null $props = null,
		string|array|null $html = null,
		string|null $text = null
	): static {
		return new static(
			html: $html ?? $props['html'] ?? $this->html(),
			text: $text ?? $props['text'] ?? $this->text()
		);
	}

	/**
	 * Returns the HTML content of the email body
	 */
	public function html(): string
	{
		return $this->html;
	}

	/**
	 * Returns the plain text content of the email body
	 */
	public function text(): string
	{
		return $this->text;
	}

	/**
	 * Returns array of plain text and html
	 */
	public function toArray()
	{
		return [
			'text' => $this->text(),
			'html' => $this->html()
		];
	}
}
