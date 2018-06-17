<?php

namespace Kirby\Http;

use Throwable;

/**
 * Representation of an Http response,
 * to simplify sending correct headers
 * and Http status codes.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Response
{

    /**
     * Store for all registered headers,
     * which will be sent with the response
     *
     * @var array
     */
    protected $headers = [];

    /**
     * The response body
     *
     * @var string
     */
    protected $body;

    /**
     * The HTTP response code
     *
     * @var int
     */
    protected $code;

    /**
     * The content type for the response
     *
     * @var string
     */
    protected $type;

    /**
     * The content type charset
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * Creates a new response object
     *
     * @param string  $body
     * @param string  $type
     * @param integer $code
     */
    public function __construct(string $body = '', string $type = 'text/html', int $code = 200)
    {
        $this->body($body);
        $this->type($type);
        $this->code($code);
    }

    /**
     * Setter and getter for the body
     *
     * @param  string|array|null $body
     * @return string
     */
    public function body($body = null): string
    {
        if ($body === null) {
            return $this->body;
        }

        if (is_array($body)) {
            return $this->body = implode($body);
        }

        return $this->body = $body;
    }

    /**
     * Setter and getter for all headers
     *
     * The setter will overwrite already
     * set headers.
     *
     * @param  array|null $headers
     * @return array
     */
    public function headers(array $headers = null): array
    {
        if ($headers === null) {
            return $this->headers;
        }

        return $this->headers = $headers;
    }

    /**
     * Setter and getter for headers
     *
     * @param  string      $key   Name of the header
     * @param  string|null $value The header value.
     *                            Pass null to receive the current header value
     * @return string|null
     */
    public function header(string $key, string $value = null)
    {
        if ($value === null) {
            return $this->headers[$key] ?? null;
        }

        return $this->headers[$key] = $value;
    }

    /**
     * Setter and getter for the content type
     *
     * @param  string|null $type
     * @return string
     */
    public function type(string $type = null): string
    {
        if ($type === null) {
            return $this->type;
        }

        return $this->type = $type;
    }

    /**
     * Setter and getter for the content type charset
     *
     * @param  string|null $charset
     * @return string
     */
    public function charset(string $charset = null): string
    {
        if ($charset === null) {
            return $this->charset;
        }

        return $this->charset = $charset;
    }

    /**
     * Setter and getter for the HTTP status code
     *
     * @param  int|null $code
     * @return int
     */
    public function code(int $code = null): int
    {
        if ($code === null) {
            return $this->code;
        }

        return $this->code = $code;
    }

    /**
     * Sends all registered headers and
     * returns the response body
     *
     * @return string
     */
    public function send(): string
    {

        // send the status response code
        http_response_code($this->code());

        // send the content type header
        header('Content-Type:' . $this->type() . '; charset=' . $this->charset());

        // send all custom headers
        foreach ($this->headers() as $key => $value) {
            header($key . ': ' . $value);
        }

        // print the response body
        return $this->body();
    }

    /**
     * Converts all relevant response attributes
     * to an associative array for debugging,
     * testing or whatever.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'    => $this->type(),
            'charset' => $this->charset(),
            'code'    => $this->code(),
            'headers' => $this->headers(),
            'body'    => $this->body()
        ];
    }

    /**
     * Makes it possible to convert the
     * entire response object to a string
     * to send the headers and print the body
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->send();
        } catch (Throwable $e) {
            error_log($e);
            return '';
        }
    }
}
