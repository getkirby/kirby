<?php

namespace Kirby\Http;

use Exception;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * A handy little class to handle
 * all kinds of remote requests
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Remote
{

    /**
     * @var array
     */
    public static $defaults = [
        'agent'    => null,
        'body'     => true,
        'data'     => [],
        'encoding' => 'utf-8',
        'file'     => null,
        'headers'  => [],
        'method'   => 'GET',
        'progress' => null,
        'test'     => false,
        'timeout'  => 10,
    ];

    /**
     * @var string
     */
    public $content;

    /**
     * @var resource
     */
    public $curl;

    /**
     * @var array
     */
    public $curlopt = [];

    /**
     * @var int
     */
    public $errorCode;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var array
     */
    public $info = [];

    /**
     * @var array
     */
    public $options = [];

    /**
     * Magic getter for request info data
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        $method = str_replace('-', '_', Str::kebab($method));
        return $this->info[$method] ?? null;
    }

    /**
     * Constructor
     *
     * @param string $url
     * @param array $options
     */
    public function __construct(string $url, array $options = [])
    {
        // set all options
        $this->options = array_merge(static::$defaults, $options);

        // add the url
        $this->options['url'] = $url;

        // send the request
        $this->fetch();
    }

    public static function __callStatic(string $method, array $arguments = [])
    {
        return new static($arguments[0], array_merge(['method' => strtoupper($method)], $arguments[1] ?? []));
    }

    /**
     * Returns the http status code
     *
     * @return integer|null
     */
    public function code(): ?int
    {
        return $this->info['http_code'] ?? null;
    }

    /**
     * Returns the response content
     *
     * @return mixed
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Sets up all curl options and sends the request
     *
     * @return self
     */
    public function fetch()
    {

        // curl options
        $this->curlopt = [
            CURLOPT_URL              => $this->options['url'],
            CURLOPT_ENCODING         => $this->options['encoding'],
            CURLOPT_CONNECTTIMEOUT   => $this->options['timeout'],
            CURLOPT_TIMEOUT          => $this->options['timeout'],
            CURLOPT_AUTOREFERER      => true,
            CURLOPT_RETURNTRANSFER   => $this->options['body'],
            CURLOPT_FOLLOWLOCATION   => true,
            CURLOPT_MAXREDIRS        => 10,
            CURLOPT_SSL_VERIFYPEER   => false,
            CURLOPT_HEADER           => false,
            CURLOPT_HEADERFUNCTION   => function ($curl, $header) {
                $parts = Str::split($header, ':');

                if (empty($parts[0]) === false && empty($parts[1]) === false) {
                    $key = array_shift($parts);
                    $this->headers[$key] = implode(':', $parts);
                }

                return strlen($header);
            }
        ];

        // add the progress
        if (is_callable($this->options['progress']) === true) {
            $this->curlopt[CURLOPT_NOPROGRESS]       = false;
            $this->curlopt[CURLOPT_PROGRESSFUNCTION] = $this->options['progress'];
        }

        // add all headers
        if (empty($this->options['headers']) === false) {
            $this->curlopt[CURLOPT_HTTPHEADER] = $this->options['headers'];
        }

        // add the user agent
        if (empty($this->options['agent']) === false) {
            $this->curlopt[CURLOPT_USERAGENT] = $this->options['agent'];
        }

        // do some request specific stuff
        switch ($action = strtoupper($this->options['method'])) {
            case 'POST':
                $this->curlopt[CURLOPT_POST]          = true;
                $this->curlopt[CURLOPT_CUSTOMREQUEST] = 'POST';
                $this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
                break;
            case 'PUT':
                $this->curlopt[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);

                // put a file
                if ($this->options['file']) {
                    $this->curlopt[CURLOPT_INFILE]     = fopen($this->options['file'], 'r');
                    $this->curlopt[CURLOPT_INFILESIZE] = F::size($this->options['file']);
                }
                break;
            case 'PATCH':
                $this->curlopt[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                $this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
                break;
            case 'DELETE':
                $this->curlopt[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
                break;
            case 'HEAD':
                $this->curlopt[CURLOPT_CUSTOMREQUEST] = 'HEAD';
                $this->curlopt[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
                $this->curlopt[CURLOPT_NOBODY]        = true;
                break;
        }

        if ($this->options['test'] === true) {
            return $this;
        }

        // start a curl request
        $this->curl = curl_init();

        curl_setopt_array($this->curl, $this->curlopt);

        $this->content      = curl_exec($this->curl);
        $this->info         = curl_getinfo($this->curl);
        $this->errorCode    = curl_errno($this->curl);
        $this->errorMessage = curl_error($this->curl);

        if ($this->errorCode) {
            throw new Exception($this->errorMessage, $this->errorCode);
        }

        curl_close($this->curl);

        return $this;
    }

    /**
     * Static method to send a GET request
     *
     * @param string $url
     * @param array $params
     * @return self
     */
    public static function get(string $url, array $params = [])
    {
        $defaults = [
            'method' => 'GET',
            'data'   => [],
        ];

        $options = array_merge($defaults, $params);
        $query   = http_build_query($options['data']);

        if (empty($query) === false) {
            $url = Url::hasQuery($url) === true ? $url . '&' . $query : $url . '?' . $query;
        }

        // remove the data array from the options
        unset($options['data']);

        return new static($url, $options);
    }

    /**
     * Returns all received headers
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Returns the request info
     *
     * @return array
     */
    public function info(): array
    {
        return $this->info;
    }

    /**
     * Returns the request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->options['method'];
    }

    /**
     * Returns all options which have been
     * set for the current request
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Internal method to handle post field data
     *
     * @param mixed $data
     * @return mixed
     */
    protected function postfields($data)
    {
        if (is_object($data) || is_array($data)) {
            return http_build_query($data);
        } else {
            return $data;
        }
    }

    /**
     * Static method to init this class and send a request
     *
     * @param string $url
     * @param array $params
     * @return self
     */
    public static function request(string $url, array $params = [])
    {
        return new static($url, $params);
    }

    /**
     * Returns the request Url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->options['url'];
    }
}
