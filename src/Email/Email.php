<?php

namespace Kirby\Email;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Files;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Exception\NotFoundException;

/**
 * Wrapper for email libraries
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.0.0
 */
class Email
{
	protected array $attachments;
	protected array $bcc;
	protected Body $body;
	protected array $cc;
	protected Address $from;
	protected Address|null $replyTo = null;
	protected array $to;

	public bool $isSent = false;

	public function __construct(
		User|string|array $from,
		Users|array|User|string $to,
		protected string $subject,
		Files|array $attachments = [],
		string|array|null $body = null,
		protected array $data = [],
		Users|array|User|string $bcc = [],
		Users|array|User|string $cc = [],
		User|string|null $replyTo = null,
		protected string|null $template = null,
		protected array|null $transport = null,
		protected Closure|null $beforeSend = null
	) {
		$this->from        = Address::factory($from);
		$this->replyTo     = $replyTo ? Address::factory($replyTo) : null;
		$this->to          = Address::factory($to, multiple: true);
		$this->cc          = Address::factory($cc, multiple: true);
		$this->bcc         = Address::factory($bcc, multiple: true);
		$this->body        = Body::factory($body, $template, $data);
		$this->attachments = Attachment::factory($attachments);
	}

	/**
	 * Returns email attachments
	 */
	public function attachments(): array
	{
		return $this->attachments;
	}

	/**
	 * Returns BCC recipients
	 */
	public function bcc(): array
	{
		return $this->bcc;
	}

	/**
	 * Returns the closure called before sending
	 * via the email mailer library
	 */
	public function beforeSend(): Closure|null
	{
		return $this->beforeSend;
	}

	/**
	 * Returns the email body
	 */
	public function body(): Body
	{
		return $this->body;
	}

	/**
	 * Returns CC recipients
	 */
	public function cc(): array
	{
		return $this->cc;
	}

	/**
	 * Creates a new email object from props
	 * incorporating a preset and template
	 *
	 * @throws \Kirby\Exception\NotFoundException template wasn't found
	 *
	 * @psalm-suppress TooFewArguments
	 */
	public static function factory(
		string|array $preset,
		array $props = []
	): static {
		$kirby   = App::instance();
		$options = $kirby->option('email', []);

		// resolve preset
		if (is_string($preset) === true) {
			$preset = $options['presets'][$preset] ?? null;

			if ($preset === null) {
				throw new NotFoundException([
					'key'  => 'email.preset.notFound',
					'data' => ['name' => $preset]
				]);
			}
		}

		$props = [
			'transport'  => $options['transport'] ?? [],
			'beforeSend' => $options['beforeSend'] ?? null,
			...$preset,
			...$props
		];

		return new static(...$props);
	}

	/**
	 * Returns the sender email address
	 */
	public function from(): Address
	{
		return $this->from;
	}

	/**
	 * @deprecated 5.0.0 Use `$email->from()->name()` instead
	 * @codeCoverageIgnore
	 */
	public function fromName(): string|null
	{
		return $this->from()->name();
	}

	/**
	 * Checks if the email has an HTML body
	 */
	public function isHtml(): bool
	{
		return $this->body()->isHtml();
	}

	/**
	 * Checks if the email has been sent
	 */
	public function isSent(): bool
	{
		return $this->isSent;
	}

	/**
	 * Returns the replyTo email address
	 */
	public function replyTo(): Address|null
	{
		return $this->replyTo;
	}

	/**
	 * @deprecated 5.0.0 Use `$email->replyTo()?->name()` instead
	 * @codeCoverageIgnore
	 */
	public function replyToName(): string|null
	{
		return $this->replyTo()?->name();
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
		return $this->transport ?? ['type' => 'mail'];
	}
}
