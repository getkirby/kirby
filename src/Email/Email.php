<?php

namespace Kirby\Email;

use Closure;
use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\V;

/**
 * Wrapper for email libraries
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Email
{
	/**
	 * If set to `true`, the debug mode is enabled
	 * for all emails
	 */
	public static bool $debug = false;

	/**
	 * Store for sent emails when `Email::$debug`
	 * is set to `true`
	 */
	public static array $emails = [];

	protected bool $isSent = false;

	protected array $attachments;
	protected Body $body;
	protected array $bcc;
	protected Closure|null $beforeSend;
	protected array $cc;
	protected string $from;
	protected string|null $fromName;
	protected string $replyTo;
	protected string|null $replyToName;
	protected string $subject;
	protected array $to;
	protected array|null $transport;

	/**
	 * Email constructor
	 */
	public function __construct(array $props = [], bool $debug = false)
	{
		foreach (['body', 'from', 'to', 'subject'] as $required) {
			if (isset($props[$required]) === false) {
				throw new InvalidArgumentException(
					message: 'The property "' . $required . '" is required'
				);
			}
		}

		if (is_string($props['body']) === true) {
			$props['body'] = ['text' => $props['body']];
		}

		$this->attachments = $props['attachments'] ?? [];
		$this->bcc         = $this->resolveEmail($props['bcc'] ?? null);
		$this->beforeSend  = $props['beforeSend'] ?? null;
		$this->body        = new Body($props['body']);
		$this->cc          = $this->resolveEmail($props['cc'] ?? null);
		$this->from        = $this->resolveEmail($props['from'], false);
		$this->fromName    = $props['fromName'] ?? null;
		$this->replyTo     = $this->resolveEmail($props['replyTo'] ?? null, false);
		$this->replyToName = $props['replyToName'] ?? null;
		$this->subject     = $props['subject'];
		$this->to          = $this->resolveEmail($props['to']);
		$this->transport   = $props['transport'] ?? null;

		// @codeCoverageIgnoreStart
		if (static::$debug === false && $debug === false) {
			$this->send();
		} elseif (static::$debug === true) {
			static::$emails[] = $this;
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the email attachments
	 */
	public function attachments(): array
	{
		return $this->attachments;
	}

	/**
	 * Returns the email body
	 */
	public function body(): Body|null
	{
		return $this->body;
	}

	/**
	 * Returns "bcc" recipients
	 */
	public function bcc(): array
	{
		return $this->bcc;
	}

	/**
	 * Returns the beforeSend callback closure,
	 * which has access to the PHPMailer instance
	 */
	public function beforeSend(): Closure|null
	{
		return $this->beforeSend;
	}

	/**
	 * Returns "cc" recipients
	 */
	public function cc(): array
	{
		return $this->cc;
	}

	/**
	 * Creates a new instance while
	 * merging initial and new properties
	 * @deprecated 4.0.0
	 */
	public function clone(array $props = []): static
	{
		return new static(array_merge_recursive([
			'attachments'   => $this->attachments,
			'bcc'			=> $this->bcc,
			'beforeSend'    => $this->beforeSend,
			'body'			=> $this->body->toArray(),
			'cc'   			=> $this->cc,
			'from'			=> $this->from,
			'fromName'   	=> $this->fromName,
			'replyTo' 		=> $this->replyTo,
			'replyToName'	=> $this->replyToName,
			'subject'   	=> $this->subject,
			'to'   			=> $this->to,
			'transport' 	=> $this->transport
		], $props));
	}

	/**
	 * Returns default transport settings
	 */
	protected function defaultTransport(): array
	{
		return [
			'type' => 'mail'
		];
	}

	/**
	 * Returns the "from" email address
	 */
	public function from(): string
	{
		return $this->from;
	}

	/**
	 * Returns the "from" name
	 */
	public function fromName(): string|null
	{
		return $this->fromName;
	}

	/**
	 * Checks if the email has an HTML body
	 */
	public function isHtml(): bool
	{
		return empty($this->body()->html()) === false;
	}

	/**
	 * Checks if the email has been sent successfully
	 */
	public function isSent(): bool
	{
		return $this->isSent;
	}

	/**
	 * Returns the "reply to" email address
	 */
	public function replyTo(): string
	{
		return $this->replyTo;
	}

	/**
	 * Returns the "reply to" name
	 */
	public function replyToName(): string|null
	{
		return $this->replyToName;
	}

	/**
	 * Converts single or multiple email addresses to a sanitized format
	 *
	 * @throws \Exception
	 */
	protected function resolveEmail(
		string|array|null $email = null,
		bool $multiple = true
	): array|string {
		if ($email === null) {
			return $multiple === true ? [] : '';
		}

		if (is_array($email) === false) {
			$email = [$email => null];
		}

		$result = [];
		foreach ($email as $address => $name) {
			// convert simple email arrays to associative arrays
			if (is_int($address) === true) {
				// the value is the address, there is no name
				$address = $name;
				$result[$address] = null;
			} else {
				$result[$address] = $name;
			}

			// ensure that the address is valid
			if (V::email($address) === false) {
				throw new Exception(sprintf('"%s" is not a valid email address', $address));
			}
		}

		return $multiple === true ? $result : array_keys($result)[0];
	}

	/**
	 * Sends the email
	 */
	public function send(): bool
	{
		return $this->isSent = true;
	}

	/**
	 * Returns the email subject
	 */
	public function subject(): string
	{
		return $this->subject;
	}

	/**
	 * Returns the email recipients
	 */
	public function to(): array
	{
		return $this->to;
	}

	/**
	 * Returns the email transports settings
	 */
	public function transport(): array
	{
		return $this->transport ?? $this->defaultTransport();
	}

	/**
	 * @since 4.0.0
	 */
	public function toArray(): array
	{
		return [
			'attachments'   => $this->attachments(),
			'bcc'			=> $this->bcc(),
			'body'			=> $this->body()->toArray(),
			'cc'   			=> $this->cc(),
			'from'			=> $this->from(),
			'fromName'   	=> $this->fromName(),
			'replyTo' 		=> $this->replyTo(),
			'replyToName'	=> $this->replyToName(),
			'subject'   	=> $this->subject(),
			'to'   			=> $this->to(),
			'transport' 	=> $this->transport()
		];
	}
}
