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
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Email
{
    use Properties;

    /**
     * If set to `true`, the debug mode is enabled
     * for all emails
     *
     * @var bool
     */
    public static $debug = false;

    /**
     * Store for sent emails when `Email::$debug`
     * is set to `true`
     *
     * @var array
     */
    public static $emails = [];

    /**
     * @var array|null
     */
    protected $attachments;

    /**
     * @var \Kirby\Email\Body|null
     */
    protected $body;

    /**
     * @var array|null
     */
    protected $bcc;

    /**
     * @var \Closure|null
     */
    protected $beforeSend;

    /**
     * @var array|null
     */
    protected $cc;

    /**
     * @var string|null
     */
    protected $from;

    /**
     * @var string|null
     */
    protected $fromName;

    /**
     * @var string|null
     */
    protected $replyTo;

    /**
     * @var string|null
     */
    protected $replyToName;

    /**
     * @var bool
     */
    protected $isSent = false;

    /**
     * @var string|null
     */
    protected $subject;

    /**
     * @var array|null
     */
    protected $to;

    /**
     * @var array|null
     */
    protected $transport;

    /**
     * Email constructor
     *
     * @param array $props
     * @param bool $debug
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
     *
     * @return array
     */
    public function attachments(): array
    {
        return $this->attachments;
    }

    /**
     * Returns the email body
     *
     * @return \Kirby\Email\Body|null
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Returns "bcc" recipients
     *
     * @return array
     */
    public function bcc(): array
    {
        return $this->bcc;
    }

    /**
     * Returns the beforeSend callback closure,
     * which has access to the PHPMailer instance
     *
     * @return \Closure|null
     */
    public function beforeSend(): ?Closure
    {
        return $this->beforeSend;
    }

    /**
     * Returns "cc" recipients
     *
     * @return array
     */
    public function cc(): array
    {
        return $this->cc;
    }

    /**
     * Returns default transport settings
     *
     * @return array
     */
    protected function defaultTransport(): array
    {
        return [
            'type' => 'mail'
        ];
    }

    /**
     * Returns the "from" email address
     *
     * @return string
     */
    public function from(): string
    {
        return $this->from;
    }

    /**
     * Returns the "from" name
     *
     * @return string|null
     */
    public function fromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Checks if the email has an HTML body
     *
     * @return bool
     */
    public function isHtml()
    {
        return $this->body()->html() !== null;
    }

    /**
     * Checks if the email has been sent successfully
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->isSent;
    }

    /**
     * Returns the "reply to" email address
     *
     * @return string
     */
    public function replyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * Returns the "reply to" name
     *
     * @return string|null
     */
    public function replyToName(): ?string
    {
        return $this->replyToName;
    }

    /**
     * Converts single or multiple email addresses to a sanitized format
     *
     * @param string|array|null $email
     * @param bool $multiple
     * @return array|mixed|string
     * @throws \Exception
     */
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

    /**
     * Sends the email
     *
     * @return bool
     */
    public function send(): bool
    {
        return $this->isSent = true;
    }

    /**
     * Sets the email attachments
     *
     * @param array|null $attachments
     * @return $this
     */
    protected function setAttachments($attachments = null)
    {
        $this->attachments = $attachments ?? [];
        return $this;
    }

    /**
     * Sets the email body
     *
     * @param string|array $body
     * @return $this
     */
    protected function setBody($body)
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
     * @param string|array|null $bcc
     * @return $this
     */
    protected function setBcc($bcc = null)
    {
        $this->bcc = $this->resolveEmail($bcc);
        return $this;
    }

    /**
     * Sets the "beforeSend" callback
     *
     * @param \Closure|null $beforeSend
     * @return $this
     */
    protected function setBeforeSend(?Closure $beforeSend = null)
    {
        $this->beforeSend = $beforeSend;
        return $this;
    }

    /**
     * Sets "cc" recipients
     *
     * @param string|array|null $cc
     * @return $this
     */
    protected function setCc($cc = null)
    {
        $this->cc = $this->resolveEmail($cc);
        return $this;
    }

    /**
     * Sets the "from" email address
     *
     * @param string $from
     * @return $this
     */
    protected function setFrom(string $from)
    {
        $this->from = $this->resolveEmail($from, false);
        return $this;
    }

    /**
     * Sets the "from" name
     *
     * @param string|null $fromName
     * @return $this
     */
    protected function setFromName(string $fromName = null)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * Sets the "reply to" email address
     *
     * @param string|null $replyTo
     * @return $this
     */
    protected function setReplyTo(string $replyTo = null)
    {
        $this->replyTo = $this->resolveEmail($replyTo, false);
        return $this;
    }

    /**
     * Sets the "reply to" name
     *
     * @param string|null $replyToName
     * @return $this
     */
    protected function setReplyToName(string $replyToName = null)
    {
        $this->replyToName = $replyToName;
        return $this;
    }

    /**
     * Sets the email subject
     *
     * @param string $subject
     * @return $this
     */
    protected function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Sets the recipients of the email
     *
     * @param string|array $to
     * @return $this
     */
    protected function setTo($to)
    {
        $this->to = $this->resolveEmail($to);
        return $this;
    }

    /**
     * Sets the email transport settings
     *
     * @param array|null $transport
     * @return $this
     */
    protected function setTransport($transport = null)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Returns the email subject
     *
     * @return string
     */
    public function subject(): string
    {
        return $this->subject;
    }

    /**
     * Returns the email recipients
     *
     * @return array
     */
    public function to(): array
    {
        return $this->to;
    }

    /**
     * Returns the email transports settings
     *
     * @return array
     */
    public function transport(): array
    {
        return $this->transport ?? $this->defaultTransport();
    }
}
