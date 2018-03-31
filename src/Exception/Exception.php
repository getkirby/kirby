<?php

namespace Kirby\Exception;

use Kirby\Cms\App;
use Kirby\Util\Str;

class Exception extends \Exception
{

    protected $data;
    protected $httpCode;

    protected static $defaultKey = 'general';
    protected static $defaultFallback = 'An error occurred';
    protected static $defaultData = [];
    protected static $defaultHttpCode = 500;

    private static $prefix = 'error';

    public function __construct(array $args = [])
    {
        // Define the Exception key
        $key = self::$prefix . '.' . ($args['key'] ?? static::$defaultKey);

        // Set data and httpCode from provided arguments or defaults
        $this->data = $args['data'] ?? static::$defaultData;
        $this->httpCode = $args['httpCode'] ?? static::$defaultHttpCode;

        // Fallback waterfall for message string
        $message = null;

        if (class_exists(App::class)) {
            // 1. Translation for provided key in current language
            // 2. Translation for provided key in default language
            if (isset($args['key']) === true) {
                $message = App::instance()->translate(self::$prefix . '.' . $args['key']);
            }

            // 4. Translation for default key in current language
            // 5. Translation for default key in default language
            if ($message === null) {
                $message = App::instance()->translate(self::$prefix . '.' . static::$defaultKey);
            }
        }

        // 5. Provided fallback message
        // 6. Default fallback message
        if ($message === null) {
            $message = $args['fallback'] ?? static::$defaultFallback;
        }

        // Format message with passed data
        $message = Str::template($message, $this->data, '-');

        // Handover to Exception parent class constructor
        parent::__construct($message, null, $args['previous'] ?? null);

        // Set the Exception code to the key
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
