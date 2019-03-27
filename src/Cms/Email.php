<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Tpl;

/**
 * Wrapper around our PHPMailer package, which
 * handles all the magic connections between Kirby
 * and sending emails, like email templates, file
 * attachments, etc.
 */
class Email
{
    protected $options;
    protected $preset;
    protected $props;

    protected static $transform = [
        'from'        => 'user',
        'replyTo'     => 'user',
        'to'          => 'user',
        'cc'          => 'user',
        'bcc'         => 'user',
        'attachments' => 'file'
    ];

    public function __construct($preset = [], array $props = [])
    {
        $this->options = $options = App::instance()->option('email');

        // load presets from options
        $this->preset = $this->preset($preset);
        $this->props = array_merge($this->preset, $props);

        // add transport settings
        $this->props['transport'] = $this->options['transport'] ?? [];

        // transform model objects to values
        foreach (static::$transform as $prop => $model) {
            $this->transformProp($prop, $model);
        }

        // load template for body text
        $this->template();
    }

    protected function preset($preset)
    {
        // only passed props, not preset name
        if (is_string($preset) !== true) {
            return $preset;
        }

        // preset does not exist
        if (isset($this->options['presets'][$preset]) === false) {
            throw new NotFoundException([
                'key'  => 'email.preset.notFound',
                'data' => ['name' => $preset]
            ]);
        }

        return $this->options['presets'][$preset];
    }

    protected function template()
    {
        if (isset($this->props['template']) === true) {

            // prepare data to be passed to template
            $data = $this->props['data'] ?? [];

            // check if html/text templates exist
            $html = $this->getTemplate($this->props['template'], 'html');
            $text = $this->getTemplate($this->props['template'], 'text');

            if ($html->exists()) {
                $this->props['body'] = [
                    'html' => $html->render($data)
                ];

                if ($text->exists()) {
                    $this->props['body']['text'] = $text->render($data);
                }

                // fallback to single email text template
            } elseif ($text->exists()) {
                $this->props['body'] = $text->render($data);
            } else {
                throw new NotFoundException('The email template "' . $this->props['template'] . '" cannot be found');
            }
        }
    }

    protected function getTemplate(string $name, string $type = null)
    {
        return App::instance()->template('emails/' . $name, $type, 'text');
    }

    public function toArray(): array
    {
        return $this->props;
    }

    protected function transformFile($file)
    {
        return $this->transformModel($file, 'Kirby\Cms\File', 'root');
    }

    protected function transformModel($value, $class, $content)
    {
        // value is already a string
        if (is_string($value) === true) {
            return $value;
        }

        // value is a model object, get value through content method
        if (is_a($value, $class) === true) {
            return $value->$content();
        }

        // value is an array or collection, call transform on each item
        if (is_array($value) === true || is_a($value, 'Kirby\Cms\Collection') === true) {
            $models = [];
            foreach ($value as $model) {
                $models[] = $this->transformModel($model, $class, $content);
            }
            return $models;
        }
    }

    protected function transformProp($prop, $model)
    {
        if (isset($this->props[$prop]) === true) {
            $this->props[$prop] = $this->{'transform' . ucfirst($model)}($this->props[$prop]);
        }
    }

    protected function transformUser($user)
    {
        return $this->transformModel($user, 'Kirby\Cms\User', 'email');
    }
}
