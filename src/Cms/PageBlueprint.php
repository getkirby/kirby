<?php

namespace Kirby\Cms;

class PageBlueprint extends Blueprint
{

    /**
     * Creates a new page blueprint object
     * with the given props
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        parent::__construct($props);

        // normalize all available page options
        $this->props['options'] = $this->normalizeOptions(
            $props['options'] ?? true,
            // defaults
            [
                'changeSlug'     => null,
                'changeStatus'   => null,
                'changeTemplate' => null,
                'changeTitle'    => null,
                'create'         => null,
                'delete'         => null,
                'preview'        => null,
                'sort'           => null,
                'update'         => null,
            ],
            // aliases (from v2)
            [
                'status'   => 'changeStatus',
                'template' => 'changeTemplate',
                'title'    => 'changeTitle',
                'url'      => 'changeSlug',
            ]
        );

        // normalize the ordering number
        $this->props['num'] = $this->normalizeNum($props['num'] ?? 'default');

        // normalize the available status array
        $this->props['status'] = $this->normalizeStatus($props['status'] ?? null);
    }

    /**
     * Returns the page numbering mode
     *
     * @return string
     */
    public function num(): string
    {
        return $this->props['num'];
    }

    /**
     * Returns the options object
     * that handles page options and permissions
     *
     * @return array
     */
    public function options(): array
    {
        return $this->props['options'];
    }

    /**
     * Normalizes the ordering number
     *
     * @param mixed $num
     * @return string
     */
    protected function normalizeNum($num): string
    {
        $aliases = [
            0          => 'zero',
            '0'        => 'zero',
            'date'     => '{{ page.date("Ymd") }}',
            'datetime' => '{{ page.date("YmdHi") }}',
            'sort'     => 'default',
        ];

        if (isset($aliases[$num]) === true) {
            return $aliases[$num];
        }

        return $num;
    }

    /**
     * Normalizes the available status options for the page
     *
     * @param mixed $status
     * @return array
     */
    protected function normalizeStatus($status): array
    {
        $defaults = [
            'draft'    => [
                'label' => $this->i18n('page.status.draft'),
                'text'  => $this->i18n('page.status.draft.description'),
            ],
            'unlisted' => [
                'label' => $this->i18n('page.status.unlisted'),
                'text'  => $this->i18n('page.status.unlisted.description'),
            ],
            'listed' => [
                'label' => $this->i18n('page.status.listed'),
                'text'  => $this->i18n('page.status.listed.description'),
            ]
        ];

        // use the defaults, when the status is not defined
        if (is_array($status) === false) {
            $status = $defaults;
        }

        // clean up and translate each status
        foreach ($status as $key => $options) {

            // skip invalid status definitions
            if (in_array($key, ['draft', 'listed', 'unlisted']) === false || $options === false) {
                continue;
            }

            if ($options === true) {
                $status[$key] = $defaults[$key];
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
            $status[$key]['label'] = $this->i18n($status[$key]['label'], $status[$key]['label']);
            $status[$key]['text']  = $this->i18n($status[$key]['text'], $status[$key]['text']);
        }

        // the draft status is required
        if (isset($status['draft']) === false) {
            $status = ['draft' => $defaults['draft']] + $status;
        }

        return $status;
    }

    /**
     * Returns the status array
     *
     * @return array
     */
    public function status(): array
    {
        return $this->props['status'];
    }
}
