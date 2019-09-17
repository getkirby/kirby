<?php

namespace Kirby\Http;

use Kirby\Toolkit\F;

/**
 * The Header class provides methods
 * for sending HTTP headers.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Header
{
    // configuration
    public static $codes = [

        // successful
        '_200' => 'OK',
        '_201' => 'Created',
        '_202' => 'Accepted',

        // redirection
        '_300' => 'Multiple Choices',
        '_301' => 'Moved Permanently',
        '_302' => 'Found',
        '_303' => 'See Other',
        '_304' => 'Not Modified',
        '_307' => 'Temporary Redirect',
        '_308' => 'Permanent Redirect',

        // client error
        '_400' => 'Bad Request',
        '_401' => 'Unauthorized',
        '_402' => 'Payment Required',
        '_403' => 'Forbidden',
        '_404' => 'Not Found',
        '_405' => 'Method Not Allowed',
        '_406' => 'Not Acceptable',
        '_410' => 'Gone',
        '_418' => 'I\'m a teapot',
        '_451' => 'Unavailable For Legal Reasons',

        // server error
        '_500' => 'Internal Server Error',
        '_501' => 'Not Implemented',
        '_502' => 'Bad Gateway',
        '_503' => 'Service Unavailable',
        '_504' => 'Gateway Time-out'
    ];

    /**
     * Sends a content type header
     *
     * @param string $mime
     * @param string $charset
     * @param boolean $send
     * @return string|void
     */
    public static function contentType(string $mime, string $charset = 'UTF-8', bool $send = true)
    {
        if ($found = F::extensionToMime($mime)) {
            $mime = $found;
        }

        $header = 'Content-type: ' . $mime;

        if (empty($charset) === false) {
            $header .= '; charset=' . $charset;
        }

        if ($send === false) {
            return $header;
        }

        header($header);
    }

    /**
     * Creates headers by key and value
     *
     * @param string|array $key
     * @param string|null $value
     * @return string
     */
    public static function create($key, string $value = null): string
    {
        if (is_array($key) === true) {
            $headers = [];

            foreach ($key as $k => $v) {
                $headers[] = static::create($k, $v);
            }

            return implode("\r\n", $headers);
        }

        // prevent header injection by stripping any newline characters from single headers
        return str_replace(["\r", "\n"], '', $key . ': ' . $value);
    }

    /**
     * Shortcut for static::contentType()
     *
     * @param string $mime
     * @param string $charset
     * @param boolean $send
     * @return string|void
     */
    public static function type(string $mime, string $charset = 'UTF-8', bool $send = true)
    {
        return static::contentType($mime, $charset, $send);
    }

    /**
     * Sends a status header
     *
     * Checks $code against a list of known status codes. To bypass this check
     * and send a custom status code and message, use a $code string formatted
     * as 3 digits followed by a space and a message, e.g. '999 Custom Status'.
     *
     * @param int|string $code The HTTP status code
     * @param boolean $send If set to false the header will be returned instead
     * @return string|void
     */
    public static function status($code = null, bool $send = true)
    {
        $codes    = static::$codes;
        $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';

        // allow full control over code and message
        if (is_string($code) === true && preg_match('/^\d{3} \w.+$/', $code) === 1) {
            $message = substr(rtrim($code), 4);
            $code    = substr($code, 0, 3);
        } else {
            $code    = array_key_exists('_' . $code, $codes) === false ? 500 : $code;
            $message = $codes['_' . $code] ?? 'Something went wrong';
        }

        $header = $protocol . ' ' . $code . ' ' . $message;

        if ($send === false) {
            return $header;
        }

        // try to send the header
        header($header);
    }

    /**
     * Sends a 200 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function success(bool $send = true)
    {
        return static::status(200, $send);
    }

    /**
     * Sends a 201 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function created(bool $send = true)
    {
        return static::status(201, $send);
    }

    /**
     * Sends a 202 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function accepted(bool $send = true)
    {
        return static::status(202, $send);
    }

    /**
     * Sends a 400 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function error(bool $send = true)
    {
        return static::status(400, $send);
    }

    /**
     * Sends a 403 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function forbidden(bool $send = true)
    {
        return static::status(403, $send);
    }

    /**
     * Sends a 404 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function notfound(bool $send = true)
    {
        return static::status(404, $send);
    }

    /**
     * Sends a 404 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function missing(bool $send = true)
    {
        return static::status(404, $send);
    }

    /**
     * Sends a 410 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function gone(bool $send = true)
    {
        return static::status(410, $send);
    }

    /**
     * Sends a 500 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function panic(bool $send = true)
    {
        return static::status(500, $send);
    }

    /**
     * Sends a 503 header
     *
     * @param boolean $send
     * @return string|void
     */
    public static function unavailable(bool $send = true)
    {
        return static::status(503, $send);
    }

    /**
     * Sends a redirect header
     *
     * @param string $url
     * @param int $code
     * @param boolean $send
     * @return string|void
     */
    public static function redirect(string $url, int $code = 302, bool $send = true)
    {
        $status   = static::status($code, false);
        $location = 'Location:' . Url::unIdn($url);

        if ($send !== true) {
            return $status . "\r\n" . $location;
        }

        header($status);
        header($location);
        exit();
    }

    /**
     * Sends download headers for anything that is downloadable
     *
     * @param array $params Check out the defaults array for available parameters
     */
    public static function download(array $params = [])
    {
        $defaults = [
            'name'     => 'download',
            'size'     => false,
            'mime'     => 'application/force-download',
            'modified' => time()
        ];

        $options = array_merge($defaults, $params);

        header('Pragma: public');
        header('Expires: 0');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $options['modified']) . ' GMT');
        header('Content-Disposition: attachment; filename="' . $options['name'] . '"');
        header('Content-Transfer-Encoding: binary');

        static::contentType($options['mime']);

        if ($options['size']) {
            header('Content-Length: ' . $options['size']);
        }

        header('Connection: close');
    }
}
