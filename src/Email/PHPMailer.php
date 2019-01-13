<?php

namespace Kirby\Email;

use PHPMailer\PHPMailer\PHPMailer as Mailer;

/**
 * Wrapper for PHPMailer library
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
*/
class PHPMailer extends Email
{
    public function send(bool $debug = false): bool
    {
        $mailer = new Mailer(true);

        // set sender's address
        $mailer->setFrom($this->from());

        // optional reply-to address
        if ($replyTo = $this->replyTo()) {
            $mailer->addReplyTo($replyTo);
        }

        // add (multiple) recepient, CC & BCC addresses
        foreach ($this->to() as $to) {
            $mailer->addAddress($to);
        }
        foreach ($this->cc() as $cc) {
            $mailer->addCC($cc);
        }
        foreach ($this->bcc() as $bcc) {
            $mailer->addBCC($bcc);
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
        }

        if ($debug === true) {
            return $this->isSent = true;
        }

        return $this->isSent = $mailer->send();
    }
}
