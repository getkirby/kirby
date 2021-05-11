<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Form\Form as BaseForm;
use Kirby\Toolkit\Str;

/**
 * Extension of `Kirby\Form\Form` that introduces
 * a Form::for method that creates a proper form
 * definition for any Cms Model.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Form extends BaseForm
{
    protected $errors;
    protected $fields;
    protected $values = [];

    /**
     * Form constructor.
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $kirby = App::instance();

        if ($kirby->multilang() === true) {
            $fields            = $props['fields'] ?? [];
            $languageCode      = $props['language'] ?? $kirby->language()->code();
            $isDefaultLanguage = $languageCode === $kirby->defaultLanguage()->code();

            foreach ($fields as $fieldName => $fieldProps) {
                // switch untranslatable fields to readonly
                if (($fieldProps['translate'] ?? true) === false && $isDefaultLanguage === false) {
                    $fields[$fieldName]['unset']    = true;
                    $fields[$fieldName]['disabled'] = true;
                }
            }

            $props['fields'] = $fields;
        }

        parent::__construct($props);
    }

    /**
     * Get the field object by name
     * and handle nested fields correctly
     *
     * @param string $name
     * @throws \Kirby\Exception\NotFoundException
     * @return \Kirby\Form\Field
     */
    public function field(string $name)
    {
        $form       = $this;
        $fieldNames = Str::split($name, '+');
        $index      = 0;
        $count      = count($fieldNames);
        $field      = null;

        foreach ($fieldNames as $fieldName) {
            $index++;

            if ($field = $form->fields()->get($fieldName)) {
                if ($count !== $index) {
                    $form = $field->form();
                }
            } else {
                throw new NotFoundException('The field "' . $fieldName . '" could not be found');
            }
        }

        // it can get this error only if $name is an empty string as $name = ''
        if ($field === null) {
            throw new NotFoundException('No field could be loaded');
        }

        return $field;
    }

    /**
     * @param \Kirby\Cms\Model $model
     * @param array $props
     * @return static
     */
    public static function for(Model $model, array $props = [])
    {
        // get the original model data
        $original = $model->content($props['language'] ?? null)->toArray();
        $values   = $props['values'] ?? [];

        // convert closures to values
        foreach ($values as $key => $value) {
            if (is_a($value, 'Closure') === true) {
                $values[$key] = $value($original[$key] ?? null);
            }
        }

        // set a few defaults
        $props['values'] = array_merge($original, $values);
        $props['fields'] = $props['fields'] ?? [];
        $props['model']  = $model;

        // search for the blueprint
        if (method_exists($model, 'blueprint') === true && $blueprint = $model->blueprint()) {
            $props['fields'] = $blueprint->fields();
        }

        $ignoreDisabled = $props['ignoreDisabled'] ?? false;

        // REFACTOR: this could be more elegant
        if ($ignoreDisabled === true) {
            $props['fields'] = array_map(function ($field) {
                $field['disabled'] = false;
                return $field;
            }, $props['fields']);
        }

        return new static($props);
    }
}
