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
	use Properties;

	protected string|null $html = null;
	protected string|null $text = null;

	/**
	 * Email body constructor
	 */
	public function __construct(array $props = [])
	{
		$this->setProperties($props);
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
	 * Sets the HTML content for the email body
	 *
	 * @return $this
	 */
	protected function setHtml(string|null $html = null): static
	{
		$this->html = $html;
		return $this;
	}

	/**
	 * Sets the plain text content for the email body
	 *
	 * @return $this
	 */
	protected function setText(string|null $text = null): static
	{
		$this->text = $text;
		return $this;
	}
}
