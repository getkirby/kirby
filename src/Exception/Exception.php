<?php

namespace Kirby\Exception;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class Exception extends \Exception
{
    protected $data;
    protected $httpCode;
    protected $details;
    protected $isTranslated = true;

    protected static $defaultKey = 'general';
    protected static $defaultFallback = 'An error occurred';
    protected static $defaultData = [];
    protected static $defaultHttpCode = 500;
    protected static $defaultDetails = [];

    private static $prefix = 'error';

    public function __construct($args = [])
    {
        // Set data and httpCode from provided arguments or defaults
        $this->data = $args['data'] ?? static::$defaultData;
        $this->httpCode = $args['httpCode'] ?? static::$defaultHttpCode;
        $this->details = $args['details'] ?? static::$defaultDetails;

        if (is_string($args) === true) {
            $this->isTranslated = false;
            parent::__construct($args);
        } else {
            // Define whether message can/should be translated
            $translate = ($args['translate'] ?? true) === true && class_exists('Kirby\Cms\App') === true;

            // Define the Exception key
            $key = self::$prefix . '.' . ($args['key'] ?? static::$defaultKey);

            // Fallback waterfall for message string
            $message = null;

            if ($translate) {
                // 1. Translation for provided key in current language
                // 2. Translation for provided key in default language
                if (isset($args['key']) === true) {
                    $message = I18n::translate(self::$prefix . '.' . $args['key']);
                    $this->isTranslated = true;
                }
            }

            // 3. Provided fallback message
            if ($message === null) {
                $message = $args['fallback'] ?? null;
                $this->isTranslated = false;
            }

            if ($translate) {
                // 4. Translation for default key in current language
                // 5. Translation for default key in default language
                if ($message === null) {
                    $message = I18n::translate(self::$prefix . '.' . static::$defaultKey);
                    $this->isTranslated = true;
                }
            }

            // 6. Default fallback message
            if ($message === null) {
                $message = static::$defaultFallback;
                $this->isTranslated = false;
            }

            // Format message with passed data
            $message = Str::template($message, $this->data, '-', '{', '}');

            // Handover to Exception parent class constructor
            parent::__construct($message, null, $args['previous'] ?? null);

            // Set the Exception code to the key
            $this->code = $key;
        }
    }

    final public function getData(): array
    {
        return $this->data;
    }

    final public function getDetails(): array
    {
        return $this->details;
    }

    final public function getKey(): string
    {
        return $this->getCode();
    }

    final public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    final public function isTranslated(): bool
    {
        return $this->isTranslated;
    }

    public function toArray(): array
    {
        return [
            'exception' => static::class,
            'message'   => $this->getMessage(),
            'key'       => $this->getKey(),
            'file'      => ltrim($this->getFile(), $_SERVER['DOCUMENT_ROOT'] ?? null),
            'line'      => $this->getLine(),
            'details'   => $this->getDetails(),
            'code'      => $this->getHttpCode()
        ];
    }
}
