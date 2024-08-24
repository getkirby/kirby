<?php

namespace Kirby\Email;

use Closure;
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
		if ($this->transport()->type() === 'smtp') {
			$mailer->isSMTP();
			$mailer->Host       = $this->transport()->host();
			$mailer->SMTPAuth   = $this->transport()->auth();
			$mailer->Username   = $this->transport()->username();
			$mailer->Password   = $this->transport()->password();
			$mailer->SMTPSecure = $this->transport()->security();
			$mailer->Port       = $this->transport()->port();
		}

		// accessible phpMailer instance
		$beforeSend = $this->beforeSend();

		if ($beforeSend instanceof Closure) {
			$mailer = $beforeSend->call($this, $mailer) ?? $mailer;

			if ($mailer instanceof Mailer === false) {
				throw new InvalidArgumentException('"beforeSend" option return should be instance of PHPMailer\PHPMailer\PHPMailer class');
			}
		}

		if ($debug === true) {
			return $this->isSent = true;
		}

		return $this->isSent = $mailer->send(); // @codeCoverageIgnore
	}
}
