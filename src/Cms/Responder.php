<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Mime;
use Kirby\Toolkit\Str;

/**
 * Global response configuration
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Responder
{
    /**
     * HTTP status code
     *
     * @var integer
     */
    protected $code = null;

    /**
     * Response body
     *
     * @var string
     */
    protected $body = null;

    /**
     * HTTP headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Content type
     *
     * @var string
     */
    protected $type = null;

    /**
     * Creates and sends the response
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->send();
    }

    /**
     * Setter and getter for the response body
     *
     * @param string $body
     * @return string|self
     */
    public function body(string $body = null)
    {
        if ($body === null) {
            return $this->body;
        }

        $this->body = $body;
        return $this;
    }

    /**
     * Setter and getter for the status code
     *
     * @param integer $code
     * @return integer|self
     */
    public function code(int $code = null)
    {
        if ($code === null) {
            return $this->code;
        }

        $this->code = $code;
        return $this;
    }

    /**
     * Construct response from an array
     *
     * @param array $response
     */
    public function fromArray(array $response): void
    {
        $this->body($response['body'] ?? null);
        $this->code($response['code'] ?? null);
        $this->headers($response['headers'] ?? null);
        $this->type($response['type'] ?? null);
    }

    /**
     * Setter and getter for a single header
     *
     * @param string $key
     * @param string|false|null $value
     * @return string|self
     */
    public function header(string $key, $value = null)
    {
        if ($value === null) {
            return $this->headers[$key] ?? null;
        }

        if ($value === false) {
            unset($this->headers[$key]);
            return $this;
        }

        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Setter and getter for all headers
     *
     * @param array $headers
     * @return array|self
     */
    public function headers(array $headers = null)
    {
        if ($headers === null) {
            return $this->headers;
        }

        $this->headers = $headers;
        return $this;
    }

    /**
     * Shortcut to configure a json response
     *
     * @param array $json
     * @return string|self
     */
    public function json(array $json = null)
    {
        if ($json !== null) {
            $this->body(json_encode($json));
        }

        return $this->type('application/json');
    }

    /**
     * Shortcut to create a redirect response
     *
     * @param string|null $location
     * @param integer|null $code
     * @return self
     */
    public function redirect(?string $location = null, ?int $code = null)
    {
        $location = Url::to($location ?? '/');
        $location = Url::unIdn($location);

        return $this
            ->header('Location', (string)$location)
            ->code($code ?? 302);
    }

    /**
     * Creates and returns the response object from the config
     *
     * @param string|null $body
     * @return \Kirby\Cms\Response
     */
    public function send(string $body = null)
    {
        if ($body !== null) {
            $this->body($body);
        }

        return new Response($this->toArray());
    }

    /**
     * Converts the response configuration
     * to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'body'    => $this->body,
            'code'    => $this->code,
            'headers' => $this->headers,
            'type'    => $this->type,
        ];
    }

    /**
     * Setter and getter for the content type
     *
     * @param string $type
     * @return string|self
     */
    public function type(string $type = null)
    {
        if ($type === null) {
            return $this->type;
        }

        if (Str::contains($type, '/') === false) {
            $type = Mime::fromExtension($type);
        }

        $this->type = $type;
        return $this;
    }
}
