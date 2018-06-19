<?php

namespace Kirby\Http\Response;

use Kirby\Http\Response;
use Kirby\Http\Url;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Redirect extends Response
{

    /**
     * The redirect location
     *
     * @var string
     */
    protected $location;

    /**
     * Creates a new redirect object
     *
     * @param string   $location
     * @param integer  $code
     */
    public function __construct(string $location = '/', int $code = 301)
    {
        $this->location($location);
        $this->code($code);
    }

    /**
     * Setter and getter for the redirect URL
     *
     * @param  string|null $location
     * @return string
     */
    public function location(string $location = null): string
    {
        if ($location === null) {
            return $this->location;
        }

        return $this->location = Url::unIdn($location);
    }

    /**
     * Sends the redirect headers
     * and returns an empty body
     *
     * @return string
     */
    public function send(): string
    {

        // send the status response code
        http_response_code($this->code());

        // send the location to redirect
        header('Location:' . $this->location());

        // send an empty body
        return '';
    }

    /**
     * Converts the redirect object to
     * a readable array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'location' => $this->location(),
            'code'     => $this->code()
        ];
    }
}
