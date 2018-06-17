<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;

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
            $html = new EmailTemplate($this->props['template'] . '.html', $data);
            $text = new EmailTemplate($this->props['template'] . '.text', $data);
            if ($html->exists() === true && $text->exists() === true) {
                $this->props['body'] = [
                $this->props['body'] =
                    'html' => $html->render(),
                    'text' => $text->render()
                ];

            // fallback to single email text template
            } else {
                $template = new EmailTemplate($this->props['template'], $data);
                $this->props['body'] = $template->render();
            }
        }
    }

    public function toArray(): array
    {
        return $this->props;
    }

    protected function transformFile($file)
    {
        return $this->transformModel($file, File::class, 'root');
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
        if (is_array($value) === true || is_a($value, Collection::class) === true) {
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
        return $this->transformModel($user, User::class, 'email');
    }
}
