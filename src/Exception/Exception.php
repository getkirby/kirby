<?php

namespace Kirby\Exception;

use Kirby\Cms\App;

class Exception extends \Exception
{

    protected $data;
    protected $httpCode;

    protected static $defaultKey = 'error';
    protected static $defaultFallback = 'An error occured';
    protected static $defaultData = [];
    protected static $defaultHttpCode = 0;

    public function __construct(array $args = [])
    {
        $key = 'exception.' . ($args['key'] ?? static::$defaultKey);
        $message = $args['fallback'] ?? static::$defaultFallback;
        $this->data = $args['data'] ?? static::$defaultData;
        $this->httpCode = $args['httpCode'] ?? static::$defaultHttpCode;
        $previous = $args['previous'] ?? null;

        // use localized message if can be loaded
        if (class_exists(App::class)) {
            $message = App::instance()->locales()->get($key, $message);
        }

        // format message with passed data
        $message = sprintf($message, ...$this->data);

        // handover to Exception parent class constructor
        parent::__construct($message, null, $previous);

        // set the Exception code to the key
        $this->code = $key;
    }

    final public function getData(): array
    {
        return $this->data;
    }

    final public function getKey(): string
    {
        return $this->getCode();
    }

    final public function getHttpCode(): int
    {
        return $this->httpCode;
    }

}
