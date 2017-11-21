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
     */
    public function __construct($body = '', int $code = 200)
    {
        $this->body($body);
        $this->code($code);
    }

    /**
     * Setter and getter for the body,
     * which will automatically convert
     * arrays to json
     *
     * @param  string|array|null $body
     * @return string
     */
    public function body($body = null): string
    {
        if ($body === null) {
            return parent::body();
        } elseif (is_array($body) === true) {
            return parent::body(json_encode($body));
        }

        return parent::body($body);
    }
}
