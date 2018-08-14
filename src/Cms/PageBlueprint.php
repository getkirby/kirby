<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for pages.
 */
class PageBlueprint extends Blueprint
{
    protected $num;
    protected $status;

    /**
     * Returns the page numbering mode
     *
     * @return string|null
     */
    public function num()
    {
        return $this->num;
    }

    /**
     * Returns a list of available status
     * definitions for the page.
     *
     * @return array
     */
    public function status(): array
    {
        $status   = $this->status;
        $defaults = [
            'draft'    => [
                'label' => I18n::translate('page.status.draft'),
                'text'  => I18n::translate('page.status.draft.description'),
            ],
            'unlisted' => [
                'label' => I18n::translate('page.status.unlisted'),
                'text'  => I18n::translate('page.status.unlisted.description'),
            ],
            'listed' => [
                'label' => I18n::translate('page.status.listed'),
                'text'  => I18n::translate('page.status.listed.description'),
            ]
        ];

        // use the defaults, when the status is not defined
        if (is_array($status) === false) {
            $status = $defaults;
        }

        // clean up and translate each status
        foreach ($status as $key => $options) {

            // skip invalid status definitions
            if (in_array($key, ['draft', 'listed', 'unlisted']) === false) {
                continue;
            }

            // convert everything to a simple array
            if (is_array($options) === false) {
                $status[$key] = [
                    'label' => $options,
                    'text'  => null
                ];
            }

            // always make sure to have a proper label
            if (empty($status[$key]['label']) === true) {
                $status[$key]['label'] = $defaults[$key]['label'];
            }

            // also make sure to have the text field set
            if (isset($status[$key]['text']) === false) {
                $status[$key]['text'] = null;
            }

            // translate text and label if necessary
            $status[$key]['label'] = I18n::translate($status[$key]['label'], $status[$key]['label']);
            $status[$key]['text']  = I18n::translate($status[$key]['text'], $status[$key]['text']);
        }

        // the draft status is required
        if (isset($status['draft']) === false) {
            $status = ['draft' => $defaults['draft']] + $status;
        }

        return $status;
    }

    /**
     * Returns the options object
     * that handles page options and permissions
     *
     * @return PageBlueprintOptions
     */
    public function options()
    {
        if (is_a($this->options, 'Kirby\Cms\PageBlueprintOptions') === true) {
            return $this->options;
        }

        return $this->options = new PageBlueprintOptions($this->model, $this->options);
    }

    /**
     * Setter for the numbering mode
     *
     * @param string|null $num
     * @return self
     */
    protected function setNum($num = null)
    {
        $aliases = [
            0          => 'zero',
            'date'     => '{{ page.date("Ymd") }}',
            'datetime' => '{{ page.date("YmdHi") }}',
            'sort'     => 'default',
        ];

        if (isset($aliases[$num])) {
            $num = $aliases[$num];
        }

        $this->num = $num ?? 'default';
        return $this;
    }

    /**
     * Setter for the status definition
     *
     * @param array|null $status
     * @return self
     */
    protected function setStatus(array $status = null)
    {
        $this->status = $status;
        return $this;
    }

}
