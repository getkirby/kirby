<?php

namespace Kirby\Cms;

use Kirby\Form\Form as BaseForm;

/**
 * Extension of `Kirby\Form\Form` that introduces
 * a Form::for method that creates a proper form
 * definition for any Cms Model.
 */
class Form extends BaseForm
{
    protected $errors;
    protected $fields;
    protected $values = [];

    public function __construct(array $props)
    {
        $kirby = App::instance();

        if ($kirby->multilang() === true) {
            $fields            = $props['fields'] ?? [];
            $isDefaultLanguage = $kirby->language()->isDefault();

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

    public static function for(Model $model, array $props = [])
    {
        // get the original model data
        $original = $model->content()->toArray();

        // set a few defaults
        $props['values'] = array_merge($original, $props['values'] ?? []);
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
