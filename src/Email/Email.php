<?php

namespace Kirby\Email;

use Closure;
use Exception;
use Kirby\Cms\Helpers;
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

	protected array $attachments;
	protected Body $body;
	protected array $bcc;
	protected Closure|null $beforeSend;
	protected array $cc;
	protected string $from;
	protected string|null $fromName;
	protected string|null $replyTo;
	protected string|null $replyToName;
	protected bool $isSent = false;
	protected string $subject;
	protected array $to;
	protected array $transport;

	/**
	 * Email constructor
	 *
	 * @todo Drop support for $props array,
	 * 		 make parameters required for required props
	 */
	public function __construct(
		// legacy parameters
		array $props = null,
		bool $debug = false,
		// new named parameters
		array $attachments = null,
		string|array $body = null,
		string|array $bcc = null,
		Closure $beforeSend = null,
		string|array $cc = null,
		string $from = null,
		string $fromName = null,
		string $replyTo = null,
		string $replyToName = null,
		string $subject = null,
		string|array $to = null,
		string|array $transport = null,
	) {
		// support deprecated $props array
		// TODO: add deprecation warning at some point
		// @codeCoverageIgnoreStart
		if (is_array($props) === true) {
			$attachments ??= $props['attachments'] ?? null;
			$body 		 ??= $props['body'] ?? null;
			$bcc 		 ??= $props['bcc'] ?? null;
			$beforeSend  ??= $props['beforeSend'] ?? null;
			$cc 		 ??= $props['cc'] ?? null;
			$from 		 ??= $props['from'] ?? null;
			$fromName 	 ??= $props['fromName'] ?? null;
			$replyTo 	 ??= $props['replyTo'] ?? null;
			$replyToName ??= $props['replyToName'] ?? null;
			$subject 	 ??= $props['subject'] ?? null;
			$to 		 ??= $props['to'] ?? null;
			$transport   ??= $props['transport'] ?? null;
		}

		// TODO: remove once parameters are non-optional
		if ($from === null || $subject === null) {
			throw new InvalidArgumentException('$from, $subject are required');
		}
		// @codeCoverageIgnoreEnd

		// normalize parameters
		if (is_string($body) === true) {
			$body = ['text' => $body];
		}

		// assign props
		$this->attachments = $attachments ?? [];
		$this->body 	   = new Body($body);
		$this->bcc         = $this->resolveEmail($bcc);
		$this->beforeSend  = $beforeSend;
		$this->cc	       = $this->resolveEmail($cc);
		$this->from	       = $this->resolveEmail($from, false);
		$this->fromName	   = $fromName;
		$this->replyTo	   = $this->resolveEmail($replyTo, false);
		$this->replyToName = $replyToName;
		$this->subject     = $subject;
		$this->to	       = $this->resolveEmail($to);
		$this->transport   = $transport ?? ['type' => 'mail'];

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
	 * Clone the email instance and
	 * pass modified properties
	 */
	public function clone(
		// legacy parameters
		array $props = null,
		// new named parameters
		array $attachments = null,
		string|array $body = null,
		string|array $bcc = null,
		Closure $beforeSend = null,
		string|array $cc = null,
		string $from = null,
		string $fromName = null,
		string $replyTo = null,
		string $replyToName = null,
		string $subject = null,
		string|array $to = null,
		string|array $transport = null,
	): static {
		// gather property values for new instance:
		// passed value overrules value from legacy $props
		// overrules old value from cloned properties
		$args = [
			'attachments' 	=> $attachments ??
							   $props['attachments'] ??
							   $this->attachments(),
			'body' 			=> $body ??
							   $props['body'] ??
							   $this->body()?->toArray(),
			'bcc' 			=> $bcc ??
							   $props['bcc'] ??
							   $this->bcc(),
			'beforeSend'	=> $beforeSend ??
							   $props['beforeSend'] ??
							   $this->beforeSend(),
			'cc'			=> $cc ??
							   $props['cc'] ??
							   $this->cc(),
			'from'			=> $from ??
							   $props['from'] ??
							   $this->from(),
			'fromName'		=> $fromName ??
							   $props['fromName'] ??
							   $this->fromName(),
			'replyTo'		=> $replyTo ??
							   $props['replyTo'] ??
							   $this->replyTo(),
			'replyToName'	=> $replyToName ??
							   $props['replyToName'] ??
							   $this->replyToName(),
			'subject'		=> $subject ??
							   $props['subject'] ??
							   $this->subject(),
			'to'			=> $to ??
							   $props['to'] ??
							   $this->to(),
			'transport'		=> $transport ??
							   $props['transport'] ??
							   $this->transport()
		];

		// remove null values and create new instance
		return new static(...array_filter($args));
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
	 * Creates an exact copy clone of
	 * the existing instance
	 *
	 * @deprecated 3.8.0 Use `->clone()` instead
	 * @todo Remove in 3.9.0
	 * @codeCoverageIgnore
	 */
	public function hardcopy(): static
	{
		Helpers::deprecated('$email->hardcopy has been deprecated and will be remove in Kirby 3.9.0. Use $email->clone() instead.');
		return $this->clone();
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
		return $this->transport;
	}
}
