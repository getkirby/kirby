<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * A collection of fieldsets
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Fieldsets extends Items
{
    public const ITEM_CLASS = '\Kirby\Cms\Fieldset';

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
            $fieldset['type'] ??= $type;

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

    public static function factory(array $items = null, array $params = [])
    {
        $items ??= option('blocks.fieldsets', [
            'code'     => 'blocks/code',
            'gallery'  => 'blocks/gallery',
            'heading'  => 'blocks/heading',
            'image'    => 'blocks/image',
            'line'     => 'blocks/line',
            'list'     => 'blocks/list',
            'markdown' => 'blocks/markdown',
            'quote'    => 'blocks/quote',
            'text'     => 'blocks/text',
            'video'    => 'blocks/video',
        ]);

        $result = static::createFieldsets($items);

        return parent::factory($result['fieldsets'], ['groups' => $result['groups']] + $params);
    }

    public function groups(): array
    {
        return $this->options['groups'] ?? [];
    }

    public function toArray(?Closure $map = null): array
    {
        return A::map(
            $this->data,
            $map ?? fn ($fieldset) => $fieldset->toArray()
        );
    }
}
