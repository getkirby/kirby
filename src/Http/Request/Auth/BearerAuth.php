<?php

namespace Kirby\Http\Request\Auth;

/**
 * Bearer Auth
 */
class BearerAuth
{
    /**
     * @var string
     */
    protected $token;

    /**
     * Creates a new Bearer Auth object
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Converts the object to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return ucfirst($this->type()) . ' ' . $this->token();
    }

    /**
     * Returns the authentication token
     *
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * Returns the auth type
     *
     * @return string
     */
    public function type(): string
    {
        return 'bearer';
    }
}
