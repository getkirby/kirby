<?php

namespace Kirby\Email;

use Kirby\Toolkit\Properties;

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
	protected string|null $html;
	protected string|null $text;

	/**
	 * Email body constructor
	 */
	public function __construct(array $props = [])
	{
		$this->html = $props['html'] ?? null;
		$this->text = $props['text'] ?? null;
	}

	/**
	 * Creates a new instance while
	 * merging initial and new properties
	 * @deprecated 4.0.0
	 */
	public function clone(array $props = []): static
	{
		return new static(array_merge_recursive([
			'html' => $this->html,
			'text' => $this->text
		], $props));
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
	 */
	public function toArray(): array
	{
		return [
			'html' => $this->html(),
			'text' => $this->text()
		];
	}
}
