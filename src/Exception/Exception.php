<?php

namespace Kirby\Exception;

use Kirby\Cms\App;

class Exception extends \Exception
{

    protected $data;
    protected $key;

    protected static $defaultKey = 'general';
    protected static $defaultData = [];
    protected static $defaultFallback = 'An error occured';
    protected static $defaultCode = null;

    public function __construct(array $args = [])
    {
        $this->key = 'exception.' . ($args['key'] ?? static::$defaultKey);
        $this->data = $args['data'] ?? static::$defaultData;
        $message = $args['fallback'] ?? static::$defaultFallback;
        $code = $args['code'] ?? static::$defaultCode;
        $previous = $args['previous'] ?? null;

        // use localized message if can be loaded
        if (class_exists(App::class)) {
            $message = App::instance()->locales()->get($this->key, $message);
        }

        // format message with passed data
        $message = sprintf($message, ...$this->data);

        // handover to Exception parent class constructor
        parent::__construct($message, $code, $previous);
    }

    final public function getKey(): string
    {
        return $this->key;
    }

    final public function getData(): array
    {
        return $this->data;
    }

}
