<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\I18n;

/**
 * Representation of all tabs in a blueprint.
 * It handles additional normalization of each
 * tab.
 */
class BlueprintTabs
{
    protected $blueprint;
    protected $tabs = [];

    public function __construct(Blueprint $blueprint, array $tabs)
    {
        $this->blueprint = $blueprint;
        $this->tabs      = $tabs;
    }

    public function columns(array $columns): array
    {
        $result = [];

        foreach ($columns as $column) {
            if (is_array($column) === false) {
                continue;
            }

            $sections = current($column);
            $width    = key($column);

            // available column widths
            $widths = [
                '1/1',
                '2/2',
                '3/3',
                '4/4',
                '6/6',
                '1/2',
                '2/4',
                '3/6',
                '1/3',
                '2/6',
                '2/3',
                '4/6',
                '1/4',
                '1/6',
                '5/6',
                '3/4',
            ];

            if (in_array($width, $widths) === false) {
                throw new InvalidArgumentException([
                    'key'  => 'blueprint.tab.columns.width.invalid',
                    'data' => ['width' => $width]
                ]);
            }

            if (empty($sections) === true) {
                throw new InvalidArgumentException([
                    'key' => 'blueprint.tab.section.missing',
                ]);
            }

            $result[] = [
                'sections' => $this->sections(Str::split($sections)),
                'width'    => $width,
            ];
        }

        return $result;
    }

    public function sections(array $sectionNames): array
    {
        $sections = [];

        foreach ($sectionNames as $sectionName) {
            $sectionObject = $this->blueprint->section($sectionName);

            $sections[$sectionName] = [
                'name' => $sectionObject->name(),
                'type' => $sectionObject->type()
            ];
        }

        return $sections;
    }

    public function tabs(array $tabs): array
    {
        $result = [];

        foreach ($tabs as $name => $tab) {
            if (is_string($name) === false) {
                throw new InvalidArgumentException([
                    'key' => 'blueprint.tab.name.missing',
                ]);
            }

            $tab = Blueprint::extend($tab);

            if (empty($tab['label']) === true) {
                throw new InvalidArgumentException([
                    'key' => 'blueprint.tab.label.missing',
                ]);
            }

            if (empty($tab['columns']) === true) {
                throw new InvalidArgumentException([
                    'key' => 'blueprint.tab.columns.missing',
                ]);
            }

            // use the key as name if the name is not already set
            $tab['name'] = $tab['name'] ?? $name;

            // convert all columns
            $tab['columns'] = $this->columns($tab['columns']);

            // translate the label if necessary
            $tab['label'] = I18n::translate($tab['label'], $tab['label']);

            ksort($tab);

            $result[$tab['name']] = $tab;
        }

        return $result;
    }

    public function toArray(): array
    {
        return array_values($this->tabs($this->tabs));
    }
}
