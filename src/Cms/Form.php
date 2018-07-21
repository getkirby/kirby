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
    public static function for(Model $model, array $props = [])
    {

        // set a few defaults
        $props['values'] = array_merge($model->content()->toArray(), $props['values'] ?? []);
        $props['fields'] = $props['fields'] ?? [];
        $props['model']  = $model;

        // search for the blueprint
        if (method_exists($model, 'blueprint') === true && $blueprint = $model->blueprint()) {
            $props['fields'] = $blueprint->fields()->toArray();

            // add the title field for sites and pages
            if (isset($props['fields']['title']) === false) {
                if (is_a($model, 'Kirby\Cms\Page') === true || is_a($model, 'Kirby\Cms\Site')) {
                    $props['fields']['title'] = [
                        'type' => 'hidden'
                    ];
                }
            }
        }

        // create generic fields for each value
        if (empty($props['fields'])) {
            $props['fields'] = [];

            foreach ($props['values'] as $name => $value) {
                $props['fields'][$name] = [
                    'type' => 'hidden'
                ];
            }
        }

        return new static($props);
    }
}
