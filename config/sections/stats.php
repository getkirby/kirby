<?php

use Kirby\Toolkit\I18n;

return [
    'mixins' => [
        'headline',
    ],
    'props' => [
        /**
         * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link` and `theme` settings.
         */
        'reports' => function ($reports = null) {
            if ($reports === null) {
                return [];
            }

            if (is_string($reports) === true) {
                $reports = $this->model()->query($reports);
            }

            if (is_array($reports) === false) {
                return [];
            }

            return $reports;
        },
        /**
         * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
         */
        'size' => function (string $size = 'large') {
            return $size;
        }
    ],
    'computed' => [
        'reports' => function () {
            $reports = [];
            $model   = $this->model();
            $value   = fn ($value) => $value === null ? null : $model->toString($value);

            foreach ($this->reports as $report) {
                if (is_string($report) === true) {
                    $report = $model->query($report);
                }

                if (is_array($report) === false) {
                    continue;
                }

                $reports[] = [
                    'label' => I18n::translate($report['label'], $report['label']),
                    'value' => $value($report['value'] ?? null),
                    'info'  => $value($report['info'] ?? null),
                    'link'  => $value($report['link'] ?? null),
                    'theme' => $value($report['theme'] ?? null)
                ];
            }

            return $reports;
        }
    ]
];
