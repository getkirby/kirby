<?php

namespace Kirby\Email;

/**
 * Representation of an email body
 * with a text and optional html version
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Body
{
	protected string|null $html;
	protected string|null $text;

	/**
	 * @param array{html?: string|null, text?: string|null} $props
	 */
	public function __construct(array $props = [])
	{
		$this->html = $props['html'] ?? null;
		$this->text = $props['text'] ?? null;
	}

	/**
	 * Returns the HTML content of the email body
	 */
	public function html(): string
	{
		return $this->html ?? '';
	}

	/**
	 * Returns the plain text content of the email body
	 */
	public function text(): string
	{
		return $this->text ?? '';
	}

	/**
	 * @since 4.0.0
	 *
	 * @return array{html: string, text: string}
	 */
	public function toArray(): array
	{
		return [
			'html' => $this->html(),
			'text' => $this->text()
		];
	}
}
