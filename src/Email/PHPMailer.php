<?php

namespace Kirby\Email;

use Kirby\Exception\InvalidArgumentException;
use PHPMailer\PHPMailer\PHPMailer as Mailer;

/**
 * Wrapper for PHPMailer library
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class PHPMailer extends Email
{
	/**
	 * Sends email via PHPMailer library
	 *
	 * @param bool $debug
	 * @return bool
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function send(bool $debug = false): bool
	{
		$mailer = new Mailer(true);

		// set sender's address
		$mailer->setFrom($this->from(), $this->fromName() ?? '');

		// optional reply-to address
		if ($replyTo = $this->replyTo()) {
			$mailer->addReplyTo($replyTo, $this->replyToName() ?? '');
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
		if ($this->isHtml()) {
			$mailer->isHTML(true);
			$mailer->Body = $this->body()->html();
			$mailer->AltBody = $this->body()->text();
		} else {
			$mailer->Body = $this->body()->text();
		}

		// add attachments
		foreach ($this->attachments() as $attachment) {
			$mailer->addAttachment($attachment);
		}

		// smtp transport settings
		if (($this->transport()['type'] ?? 'mail') === 'smtp') {
			$mailer->isSMTP();
			$mailer->Host       = $this->transport()['host'] ?? null;
			$mailer->SMTPAuth   = $this->transport()['auth'] ?? false;
			$mailer->Username   = $this->transport()['username'] ?? null;
			$mailer->Password   = $this->transport()['password'] ?? null;
			$mailer->SMTPSecure = $this->transport()['security'] ?? 'ssl';
			$mailer->Port       = $this->transport()['port'] ?? null;

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
							'Could not automatically detect the "security" protocol from the ' .
							'"port" option, please set it explicitly to "tls" or "ssl".'
						);
				}
			}
		}

		// accessible phpMailer instance
		$beforeSend = $this->beforeSend();

		if (empty($beforeSend) === false && is_a($beforeSend, 'Closure') === true) {
			$mailer = $beforeSend->call($this, $mailer) ?? $mailer;

			if (is_a($mailer, 'PHPMailer\PHPMailer\PHPMailer') === false) {
				throw new InvalidArgumentException('"beforeSend" option return should be instance of PHPMailer\PHPMailer\PHPMailer class');
			}
		}

		if ($debug === true) {
			return $this->isSent = true;
		}

		return $this->isSent = $mailer->send(); // @codeCoverageIgnore
	}
}
