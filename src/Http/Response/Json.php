<?php

namespace Kirby\Http\Response;

use Kirby\Http\Response;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Json extends Response
{

    /**
     * The default type for json
     * responses is application/json,
     * but can be changed with the
     * type setter
     *
     * @var string
     */
    protected $type = 'application/json';

    /**
     * Creates a new Json Response object
     * As body you can pass a json encoded
     * array or a plain array
     *
     * @param string|array $body
     * @param int          $code
     * @param bool         $pretty
     */
    public function __construct($body = '', int $code = 200, bool $pretty = false)
    {
        $this->body($body, $pretty);
        $this->code($code);
    }

    /**
     * Setter and getter for the body,
     * which will automatically convert
     * arrays to json
     *
     * @param  string|array|null $body
     * @param  bool              $pretty
     * @return string
     */
    public function body($body = null, bool $pretty = false): string
    {
        if ($body === null) {
            return parent::body();
        }

        if (is_array($body) === true) {
            return parent::body(json_encode($body, $pretty === true ? JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES : null));
        }

        return parent::body($body);
    }
}
