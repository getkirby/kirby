<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * A collection of fieldsets
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Fieldsets extends Items
{
    const ITEM_CLASS = '\Kirby\Cms\Fieldset';

    protected static function createFieldsets($params)
    {
        $fieldsets = [];
        $groups = [];

        foreach ($params as $type => $fieldset) {
            if (is_int($type) === true && is_string($fieldset)) {
                $type     = $fieldset;
                $fieldset = 'blocks/' . $type;
            }

            if ($fieldset === false) {
                continue;
            }

            if ($fieldset === true) {
                $fieldset = 'blocks/' . $type;
            }

            $fieldset = Blueprint::extend($fieldset);

            // make sure the type is always set
            $fieldset['type'] = $fieldset['type'] ?? $type;

            // extract groups
            if ($fieldset['type'] === 'group') {
                $result    = static::createFieldsets($fieldset['fieldsets'] ?? []);
                $fieldsets = array_merge($fieldsets, $result['fieldsets']);
                $label     = $fieldset['label'] ?? Str::ucfirst($type);

                $groups[$type] = [
                    'label'     => I18n::translate($label, $label),
                    'name'      => $type,
                    'open'      => $fieldset['open'] ?? true,
                    'sets'      => array_column($result['fieldsets'], 'type'),
                ];
            } else {
                $fieldsets[$fieldset['type']] = $fieldset;
            }
        }

        return [
            'fieldsets' => $fieldsets,
            'groups'    => $groups
        ];
    }

    public static function factory(array $fieldsets = null, array $options = [])
    {
        $fieldsets = $fieldsets ?? option('blocks.fieldsets', [
            'code'     => 'blocks/code',
            'gallery'  => 'blocks/gallery',
            'heading'  => 'blocks/heading',
            'image'    => 'blocks/image',
            'list'     => 'blocks/list',
            'markdown' => 'blocks/markdown',
            'quote'    => 'blocks/quote',
            'text'     => 'blocks/text',
            'video'    => 'blocks/video',
        ]);

        $result = static::createFieldsets($fieldsets);

        return parent::factory($result['fieldsets'], ['groups' => $result['groups']] + $options);
    }

    public function groups(): array
    {
        return $this->options['groups'] ?? [];
    }

    public function toArray(?Closure $map = null): array
    {
        return array_map($map ?? function ($fieldset) {
            return $fieldset->toArray();
        }, $this->data);
    }
}
