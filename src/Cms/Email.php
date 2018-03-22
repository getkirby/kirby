<?php

namespace Kirby\Cms;



class Email
{
    protected $preset;
    protected $props;

    public function __construct($preset = [], array $props = []) {
        $this->props = array_merge($this->preset($preset), $props);
        $this->convertObjects();
        $this->template();
    }

    protected function convertFile($file)
    {
        return $this->convertObject($file, File::class, 'url');
    }

    protected function convertObject($object, $class, $method)
    {
        if (is_string($object) === true) {
            return $object;
        }

        if (is_a($object, $class) === true) {
            return $object->$method();
        }

        if (is_array($object) === true || is_a($object, Collection::class) === true) {
            $objects = [];
            foreach ($object as $item) {
                $objects[] = $this->convertObject($item, $class, $method);
            }
            return $objects;
        }
    }

    protected function convertObjects()
    {
        $this->convertProp('from', 'user');
        $this->convertProp('replyTo', 'user');
        $this->convertProp('to', 'user');
        $this->convertProp('cc', 'user');
        $this->convertProp('bcc', 'user');
        $this->convertProp('attachments', 'file');
    }

    protected function convertProp($prop, $type) {
        if (isset($this->props[$prop]) === true) {
            $this->props[$prop] = $this->{'convert' . ucfirst($type)}($this->props[$prop]);
        }
    }

    protected function convertUser($user)
    {
        return $this->convertObject($user, User::class, 'email');
    }

    protected function preset($preset) {
        if (is_array($preset) === true) {
            return $preset;
        }

        if (is_string($preset) === true) {
            $options = App::instance()->option('email');
            return $options['presets'][$preset] ?? [];
            // TODO: throw warning if preset does not exist
        }
    }

    protected function template() {
        if (isset($this->props['template']) == true) {
            $data = $this->props['data'] ?? [];
            $template = new EmailTemplate($this->props['template'], $data);
            $this->props['body'] = $template->render();
            // TODO: implement html/text email templates
        }
    }

    public function toArray()
    {
        return $this->props;
    }

}
