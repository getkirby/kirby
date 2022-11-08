<?php

namespace Kirby\Email;

use Closure;
use Exception;
use Kirby\Toolkit\Properties;
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
	use Properties;

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

	/**
	 * @var array
	 */
	protected array|null $attachments = null;

	protected Body|null $body = null;

	/**
	 * @var array
	 */
	protected array|null $bcc = null;

	protected Closure|null $beforeSend = null;

	/**
	 * @var array
	 */
	protected array|null $cc = null;

	/**
	 * @var string
	 */
	protected string|null $from = null;
	protected string|null $fromName = null;

	protected bool $isSent = false;

	/**
	 * @var string
	 */
	protected string|null $replyTo = null;
	protected string|null $replyToName = null;

	/**
	 * @var string
	 */
	protected string|null $subject = null;

	/**
	 * @var array
	 */
	protected array|null $to = null;
	protected array|null $transport = null;

	/**
	 * Email constructor
	 */
	public function __construct(array $props = [], bool $debug = false)
	{
		$this->setProperties($props);

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
	 * Sets the email attachments
	 *
	 * @return $this
	 */
	protected function setAttachments(array|null $attachments = null): static
	{
		$this->attachments = $attachments ?? [];
		return $this;
	}

	/**
	 * Sets the email body
	 *
	 * @return $this
	 */
	protected function setBody(string|array $body): static
	{
		if (is_string($body) === true) {
			$body = ['text' => $body];
		}

		$this->body = new Body($body);
		return $this;
	}

	/**
	 * Sets "bcc" recipients
	 *
	 * @return $this
	 */
	protected function setBcc(string|array|null $bcc = null): static
	{
		$this->bcc = $this->resolveEmail($bcc);
		return $this;
	}

	/**
	 * Sets the "beforeSend" callback
	 *
	 * @return $this
	 */
	protected function setBeforeSend(Closure|null $beforeSend = null): static
	{
		$this->beforeSend = $beforeSend;
		return $this;
	}

	/**
	 * Sets "cc" recipients
	 *
	 * @return $this
	 */
	protected function setCc(string|array|null $cc = null): static
	{
		$this->cc = $this->resolveEmail($cc);
		return $this;
	}

	/**
	 * Sets the "from" email address
	 *
	 * @return $this
	 */
	protected function setFrom(string $from): static
	{
		$this->from = $this->resolveEmail($from, false);
		return $this;
	}

	/**
	 * Sets the "from" name
	 *
	 * @return $this
	 */
	protected function setFromName(string|null $fromName = null): static
	{
		$this->fromName = $fromName;
		return $this;
	}

	/**
	 * Sets the "reply to" email address
	 *
	 * @return $this
	 */
	protected function setReplyTo(string|null $replyTo = null): static
	{
		$this->replyTo = $this->resolveEmail($replyTo, false);
		return $this;
	}

	/**
	 * Sets the "reply to" name
	 *
	 * @return $this
	 */
	protected function setReplyToName(string|null $replyToName = null): static
	{
		$this->replyToName = $replyToName;
		return $this;
	}

	/**
	 * Sets the email subject
	 *
	 * @return $this
	 */
	protected function setSubject(string $subject): static
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Sets the recipients of the email
	 *
	 * @return $this
	 */
	protected function setTo(string|array $to): static
	{
		$this->to = $this->resolveEmail($to);
		return $this;
	}

	/**
	 * Sets the email transport settings
	 *
	 * @return $this
	 */
	protected function setTransport(array|null $transport = null): static
	{
		$this->transport = $transport;
		return $this;
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
}
