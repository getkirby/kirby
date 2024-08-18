<?php

namespace Kirby\Email;

use Closure;
use Kirby\Exception\InvalidArgumentException;

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
	protected Address $from;
	protected Address|null $replyTo = null;
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
				throw new InvalidArgumentException('The property "' . $required . '" is required');
			}
		}

		if (is_string($props['body']) === true) {
			$props['body'] = ['text' => $props['body']];
		}


		$this->attachments = $props['attachments'] ?? [];
		$this->bcc         = Address::factory($props['bcc'] ?? [], multiple: true);
		$this->beforeSend  = $props['beforeSend'] ?? null;
		$this->body        = new Body($props['body']);
		$this->cc          = Address::factory($props['cc'] ?? [], multiple: true);
		$this->from        = Address::factory([$props['from'] => $props['fromName'] ?? null]);
		$this->subject     = $props['subject'];
		$this->to          = Address::factory($props['to'], multiple: true);
		$this->transport   = $props['transport'] ?? null;

		if ($replyTo = $props['replyTo'] ?? null) {
			$this->replyTo = Address::factory([$replyTo => $props['replyToName'] ?? null]);
		}

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
		return Address::resolve($this->bcc);
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
		return Address::resolve($this->cc);
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
		return $this->from->email();
	}

	/**
	 * Returns the "from" name
	 */
	public function fromName(): string|null
	{
		return $this->from->name();
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
		return $this->replyTo?->email() ?? '';
	}

	/**
	 * Returns the "reply to" name
	 */
	public function replyToName(): string|null
	{
		return $this->replyTo?->name();
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
		return Address::resolve($this->to);
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
