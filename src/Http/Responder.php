<?php

namespace Kirby\Http;

use Closure;
use Exception;
use Kirby\Http\Response\Json;

/**
 * The Responder takes almost any form of input
 * and tries to convert it into a valid Response
 * object, which can then be used to send output
 * to the user
 *
 * You can customize the Responder handlers for the
 * different input types with Responder::on($type, $callback)
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Responder
{

    /**
     * All available type handlers
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Creates a new Responder object and
     * sets all default type handlers
     *
     * @param array $handlers
     */
    public function __construct(array $handlers = [])
    {
        // set all default handlers
        $this->handlers = array_merge([
            'string' => function(string $input) {
                return new Response($input);
            },
            'int' => function(int $input) {
                return new Response('', 'text/html', $input);
            },
            'array' => function(array $input) {
                return new Json($input);
            },
            'true' => function() {
                return new Response('');
            },
            'false' => function() {
                return new Response('Not found', 'text/html', 404);
            },
            'object' => function($input) {
                return new Response('Unexpected object: ' . get_class($input), 'text/html', 500);
            },
            'response' => function(Response $input) {
                return $input;
            },
            'null' => function() {
                return new Response('Not found', 'text/html', 404);
            },
            'unknown' => function($input) {
                return new Response('Unexpected input: ' . gettype($input), 'text/html', 500);
            }
        ], $handlers);
    }

    /**
     * Registers a new type handler
     *
     * @param  string  $type
     * @param  Closure $callback
     * @return Closure
     */
    public function on(string $type, Closure $callback): Closure
    {
        return $this->handlers[$type] = $callback;
    }

    /**
     * Calls a type handler for the given input
     *
     * @param  string $type
     * @param  mixed  $input
     * @return Response|Redirect
     */
    protected function trigger(string $type, $input)
    {
        if (isset($this->handlers[$type]) === false) {
            throw new Exception('Undefined Responder handler: ' . $type);
        }

        return $this->handlers[$type]->call($this, $input);
    }

    /**
     * Public method to convert any form of input
     * to a usable Response object
     * (or at least fail gracefully)
     *
     * @param  mixed    $input
     * @return Response
     */
    public function handle($input): Response
    {
        if (is_string($input) === true) {
            return $this->trigger('string', $input);
        } elseif (is_int($input) === true) {
            return $this->trigger('int', $input);
        } elseif (is_array($input) === true) {
            return $this->trigger('array', $input);
        } elseif (is_bool($input) === true) {
            if ($input === true) {
                return $this->trigger('true', $input);
            } else {
                return $this->trigger('false', $input);
            }
        } elseif (is_object($input) === true) {
            if (is_a($input, 'Kirby\Http\Response')) {
                return $this->trigger('response', $input);
            } else {
                return $this->trigger('object', $input);
            }
        } elseif (is_null($input) === true) {
            return $this->trigger('null', $input);
        } else {
            return $this->trigger('unknown', $input);
        }
    }
}
