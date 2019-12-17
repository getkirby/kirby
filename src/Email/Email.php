<?php

namespace Kirby\Email;

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
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Email
{
    use Properties;

    protected $attachments;
    protected $body;
    protected $bcc;
    protected $cc;
    protected $from;
    protected $fromName;
    protected $replyTo;
    protected $replyToName;
    protected $isSent = false;
    protected $subject;
    protected $to;
    protected $transport;

    public function __construct(array $props = [], bool $debug = false)
    {
        $this->setProperties($props);

        if ($debug === false) {
            $this->send();
        }
    }

    public function attachments(): array
    {
        return $this->attachments;
    }

    /**
     * @return \Kirby\Email\Body
     */
    public function body()
    {
        return $this->body;
    }

    public function bcc(): array
    {
        return $this->bcc;
    }

    public function cc(): array
    {
        return $this->cc;
    }

    protected function defaultTransport(): array
    {
        return [
            'type' => 'mail'
        ];
    }

    public function from(): string
    {
        return $this->from;
    }

    public function fromName(): ?string
    {
        return $this->fromName;
    }

    public function isHtml()
    {
        return $this->body()->html() !== null;
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function replyTo(): string
    {
        return $this->replyTo;
    }

    public function replyToName(): ?string
    {
        return $this->replyToName;
    }

    protected function resolveEmail($email = null, bool $multiple = true)
    {
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

    public function send(): bool
    {
        return $this->isSent = true;
    }

    protected function setAttachments($attachments = null)
    {
        $this->attachments = $attachments ?? [];
        return $this;
    }

    protected function setBody($body)
    {
        if (is_string($body) === true) {
            $body = ['text' => $body];
        }

        $this->body = new Body($body);
        return $this;
    }

    protected function setBcc($bcc = null)
    {
        $this->bcc = $this->resolveEmail($bcc);
        return $this;
    }

    protected function setCc($cc = null)
    {
        $this->cc = $this->resolveEmail($cc);
        return $this;
    }

    protected function setFrom(string $from)
    {
        $this->from = $this->resolveEmail($from, false);
        return $this;
    }

    protected function setFromName(string $fromName = null)
    {
        $this->fromName = $fromName;
        return $this;
    }

    protected function setReplyTo(string $replyTo = null)
    {
        $this->replyTo = $this->resolveEmail($replyTo, false);
        return $this;
    }

    protected function setReplyToName(string $replyToName = null)
    {
        $this->replyToName = $replyToName;
        return $this;
    }

    protected function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    protected function setTo($to)
    {
        $this->to = $this->resolveEmail($to);
        return $this;
    }

    protected function setTransport($transport = null)
    {
        $this->transport = $transport;
        return $this;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function to(): array
    {
        return $this->to;
    }

    public function transport(): array
    {
        return $this->transport ?? $this->defaultTransport();
    }
}
