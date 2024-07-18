<?php

namespace Kirby\Email;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

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
	public function __construct(
		protected string|null $html = null,
		protected string|null $text = null
	) {
	}

	public static function factory(
		string|array|null $body = null,
		string|null $template = null,
		array $data = []
	): static {
		if ($body !== null) {
			return match (true) {
				is_string($body) => new static(text: $body),
				is_array($body)  => new static(...$body),
				default          => $body
			};
		}

		if ($template !== null) {
			$kirby = App::instance();

			// check if html/text templates exist
			$html = $kirby->template('emails/' . $template, 'html', 'text');
			$text = $kirby->template('emails/' . $template, 'text', 'text');

			if ($html->exists() === false && $text->exists() === false) {
				throw new NotFoundException('The email template "' . $template . '" cannot be found');
			}

			if ($html->exists() === true) {
				if ($text->exists() === true) {
					return new static(
						html: $html->render($data),
						text: $text->render($data)
					);
				}

				return new static(html: $html->render($data));
			}

			return new static(text: $text->render($data));
		}

		throw new InvalidArgumentException('Email requires either body or template');
	}

	/**
	 * Returns the HTML content of the email body
	 */
	public function html(): string
	{
		return $this->html ?? '';
	}

	/**
	 * Checks if body is HTML
	 * @since 5.0.0
	 */
	public function isHtml(): bool
	{
		return empty($this->html()) === false;
	}

	/**
	 * Returns the plain text content of the email body
	 */
	public function text(): string
	{
		return $this->text ?? '';
	}
}
