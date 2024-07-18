<?php

namespace Kirby\Email;

use Kirby\Exception\InvalidArgumentException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Wrapper for PHPMailer library
 *
 * @package   Kirby Email
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.0.0
 */
class Mailer
{
	/**
	 * If set to `true`, the debug mode is enabled
	 * for all emails
	 */
	public static bool $debug = false;

	/**
	 * Store for sent emails when `Mailer::$debug`
	 * is set to `true`
	 */
	public static array $emails = [];


	public function __construct(
		protected Email $email,
	) {
	}

	public function send(bool $debug = false): bool
	{
		$mailer = new PHPMailer(exceptions: true);

		// set sender's address
		$mailer->setFrom(
			$this->email->from()->email(),
			$this->email->from()->name() ?? ''
		);

		// optional reply-to address
		if ($replyTo = $this->email->replyTo()) {
			$mailer->addReplyTo($replyTo->email(), $replyTo->name() ?? '');
		}

		// add (multiple) recipient, CC & BCC addresses
		foreach ($this->email->to() as $address) {
			$mailer->addAddress($address->email(), $address->name() ?? '');
		}
		foreach ($this->email->cc() as $address) {
			$mailer->addCC($address->email(), $address->name() ?? '');
		}
		foreach ($this->email->bcc() as $address) {
			$mailer->addBCC($address->email(), $address->name() ?? '');
		}

		$mailer->Subject = $this->email->subject();
		$mailer->CharSet = 'UTF-8';

		// set body according to html/text
		if ($this->email->isHtml()) {
			$mailer->isHTML(true);
			$mailer->Body = $this->email->body()->html();
			$mailer->AltBody = $this->email->body()->text();
		} else {
			$mailer->Body = $this->email->body()->text();
		}

		// add attachments
		foreach ($this->email->attachments() as $attachment) {
			$mailer->addAttachment($attachment->root());
		}

		// SMTP transport settings (incl. defaults)
		$transport = [
			'type'     => 'mail',
			'host'     => null,
			'auth'     => false,
			'username' => null,
			'password' => null,
			'security' => 'ssl',
			'port'     => null,
			...$this->email->transport()
		];

		if ($transport['type'] === 'smtp') {
			$mailer->isSMTP();
			$mailer->Host       = $transport['host'];
			$mailer->SMTPAuth   = $transport['auth'];
			$mailer->Username   = $transport['username'];
			$mailer->Password   = $transport['password'];
			$mailer->SMTPSecure = $transport['security'];
			$mailer->Port       = $transport['port'];

			if ($mailer->SMTPSecure === true) {
				switch ($mailer->Port) {
					case null:
					case 587:
						$mailer->SMTPSecure = 'tls';
						$mailer->Port = 587;
						break;
					case 465:
						$mailer->SMTPSecure = 'ssl';
						break;
					default:
						throw new InvalidArgumentException(
							'Could not automatically detect the "security" protocol from the "port" option, please set it explicitly to "tls" or "ssl".'
						);
				}
			}
		}

		// accessible phpMailer instance
		if ($beforeSend = $this->email->beforeSend()) {
			$mailer = $beforeSend->call($this, $mailer) ?? $mailer;

			if ($mailer instanceof PHPMailer === false) {
				throw new InvalidArgumentException('"beforeSend" option return must be instance of PHPMailer\PHPMailer\PHPMailer class');
			}
		}

		// debug mode
		if (static::$debug === true || $debug === true) {
			static::$emails[] = $this->email;
			return $this->email->isSent = true;
		}

		// update the email status based on the PHPMailer result
		return $this->email->isSent = $mailer->send(); // @codeCoverageIgnore
	}
}
