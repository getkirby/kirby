<?php

namespace Kirby\Email;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use PHPMailer\PHPMailer\PHPMailer as Mailer;

/**
 * Wrapper for PHPMailer library
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class PHPMailer extends Email
{
	/**
	 * Sends email via PHPMailer library
	 *
	 * @throws InvalidArgumentException
	 */
	public function send(bool $debug = false): bool
	{
		$mailer = new Mailer(true);

		// set sender's address
		$mailer->setFrom($this->from(), $this->fromName() ?? '');

		// optional reply-to address
		if ($this->replyTo() !== '') {
			$mailer->addReplyTo($this->replyTo(), $this->replyToName() ?? '');
		}

		// add (multiple) recipient, CC & BCC addresses
		foreach ($this->to() as $email => $name) {
			$mailer->addAddress($email, $name ?? '');
		}

		foreach ($this->cc() as $email => $name) {
			$mailer->addCC($email, $name ?? '');
		}

		foreach ($this->bcc() as $email => $name) {
			$mailer->addBCC($email, $name ?? '');
		}

		$mailer->Subject = $this->subject();
		$mailer->CharSet = 'UTF-8';

		// set body according to html/text
		if ($this->isHtml() === true) {
			$mailer->isHTML(true);
			$mailer->Body    = $this->body()->html();
			$mailer->AltBody = $this->body()->text();
		} else {
			$mailer->Body = $this->body()->text();
		}

		foreach ($this->attachments() as $attachment) {
			$mailer->addAttachment($attachment);
		}

		// smtp transport settings
		$transport = $this->transport();

		if (($transport['type'] ?? 'mail') === 'smtp') {
			$mailer->isSMTP();
			$mailer->Host       = $transport['host'] ?? null;
			$mailer->SMTPAuth   = $transport['auth'] ?? false;
			$mailer->Username   = $transport['username'] ?? null;
			$mailer->Password   = $transport['password'] ?? null;
			$mailer->SMTPSecure = $transport['security'] ?? 'ssl';
			$mailer->Port       = $transport['port'] ?? null;

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
							message: 'Could not automatically detect the "security" protocol from the ' .
								'"port" option, please set it explicitly to "tls" or "ssl".'
						);
				}
			}
		}

		// accessible PHPMailer instance
		$beforeSend = $this->beforeSend();

		if ($beforeSend instanceof Closure) {
			$mailer = $beforeSend->call($this, $mailer) ?? $mailer;

			if ($mailer instanceof Mailer === false) {
				throw new InvalidArgumentException(
					message: '"beforeSend" option return should be instance of PHPMailer\PHPMailer\PHPMailer class'
				);
			}
		}

		if ($debug === true) {
			return $this->isSent = true;
		}

		return $this->isSent = $mailer->send(); // @codeCoverageIgnore
	}
}
