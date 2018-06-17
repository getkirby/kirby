<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

/**
 * Wrapper around Kirby's localization files,
 * which are store in `kirby/translations`.
 */
class Translation extends Component
{
    protected $code;
    protected $data;

    public function __construct(string $code, array $data)
    {
        $this->code = $code;
        $this->data = $data;
    }

    public function author(): string
    {
        return $this->get('translation.author', 'Kirby');
    }

    public function code(): string
    {
        return $this->code;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function direction(): string
    {
        return $this->get('translation.direction', 'ltr');
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function id(): string
    {
        return $this->code;
    }

    public static function load(string $code, string $root)
    {
        try {
            return new Translation($code, Data::read($root));
        } catch (Exception $e) {
            error_log(sprintf('The translation "%s" could not be loaded', $code));
            return new Translation($code, []);
        }
    }

    public function name(): string
    {
        return $this->get('translation.name', $this->code);
    }

    public function toArray(): array
    {
        return [
            'code'   => $this->code(),
            'data'   => $this->data(),
            'name'   => $this->name(),
            'author' => $this->author(),
        ];
    }
}
