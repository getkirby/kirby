<?php

namespace Kirby\Cms;

/**
 * Converts the data from the old builder and editor fields
 * to the format supported by the new block field.
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class BlockConverter
{
    public static function builderBlock(array $params): array
    {
        if (isset($params['_key']) === false) {
            return $params;
        }

        $params['type']    = $params['_key'];
        $params['content'] = $params;
        unset($params['_uid']);

        return $params;
    }

    public static function editorBlock(array $params): array
    {
        if (static::isEditorBlock($params) === false) {
            return $params;
        }

        $method = 'editor' . $params['type'];

        if (method_exists(static::class, $method) === true) {
            $params = static::$method($params);
        } else {
            $params = static::editorCustom($params);
        }

        return $params;
    }

    public static function editorBlocks(array $blocks = []): array
    {
        if (empty($blocks) === true) {
            return $blocks;
        }

        if (static::isEditorBlock($blocks[0]) === false) {
            return $blocks;
        }

        $list = [];
        $listStart = null;

        foreach ($blocks as $index => $block) {
            if (in_array($block['type'], ['ul', 'ol']) === true) {
                $prev = $blocks[$index-1] ?? null;
                $next = $blocks[$index+1] ?? null;

                // new list starts here
                if (!$prev || $prev['type'] !== $block['type']) {
                    $listStart = $index;
                }

                // add the block to the list
                $list[] = $block;

                // list ends here
                if (!$next || $next['type'] !== $block['type']) {
                    $blocks[$listStart] = [
                        'content' => [
                            'text' =>
                                '<' . $block['type'] . '>' .
                                    implode(array_map(function ($item) {
                                        return '<li>' . $item['content'] . '</li>';
                                    }, $list)) .
                                '</' . $block['type'] . '>',
                        ],
                        'type' => 'list'
                    ];

                    $start = $listStart + 1;
                    $end   = $listStart + count($list);

                    for ($x = $start; $x <= $end; $x++) {
                        $blocks[$x] = false;
                    }

                    $listStart = null;
                    $list = [];
                }
            } else {
                $blocks[$index] = static::editorBlock($block);
            }
        }

        return array_filter($blocks);
    }

    public static function editorBlockquote(array $params): array
    {
        return [
            'content' => [
                'text' => $params['content']
            ],
            'type' => 'quote'
        ];
    }

    public static function editorCode(array $params): array
    {
        return [
            'content' => [
                'language' => $params['attrs']['language'] ?? null,
                'code'     => $params['content']
            ],
            'type' => 'code'
        ];
    }

    public static function editorCustom(array $params): array
    {
        return [
            'content' => array_merge(
                $params['attrs'] ?? [],
                [
                    'body' => $params['content'] ?? null
                ]
            ),
            'type' => $params['type'] ?? 'unknown'
        ];
    }

    public static function editorH1(array $params): array
    {
        return static::editorHeading($params, 'h1');
    }

    public static function editorH2(array $params): array
    {
        return static::editorHeading($params, 'h2');
    }

    public static function editorH3(array $params): array
    {
        return static::editorHeading($params, 'h3');
    }

    public static function editorH4(array $params): array
    {
        return static::editorHeading($params, 'h4');
    }

    public static function editorH5(array $params): array
    {
        return static::editorHeading($params, 'h5');
    }

    public static function editorH6(array $params): array
    {
        return static::editorHeading($params, 'h6');
    }

    public static function editorHr(array $params): array
    {
        return [
            'content' => [],
            'type'    => 'line'
        ];
    }

    public static function editorHeading(array $params, string $level): array
    {
        return [
            'content' => [
                'level' => $level,
                'text'  => $params['content']
            ],
            'type' => 'heading'
        ];
    }

    public static function editorImage(array $params): array
    {
        // internal image
        if (isset($params['attrs']['id']) === true) {
            return [
                'content' => [
                    'alt'      => $params['attrs']['alt'] ?? null,
                    'caption'  => $params['attrs']['caption'] ?? null,
                    'image'    => $params['attrs']['id'] ?? $params['attrs']['src'] ?? null,
                    'location' => 'kirby',
                    'ratio'    => $params['attrs']['ratio'] ?? null,
                ],
                'type' => 'image'
            ];
        }

        return [
            'content' => [
                'alt'      => $params['attrs']['alt'] ?? null,
                'caption'  => $params['attrs']['caption'] ?? null,
                'src'      => $params['attrs']['src'] ?? null,
                'location' => 'web',
                'ratio'    => $params['attrs']['ratio'] ?? null,
            ],
            'type' => 'image'
        ];
    }

    public static function editorKirbytext(array $params): array
    {
        return [
            'content' => [
                'text' => $params['content']
            ],
            'type' => 'markdown'
        ];
    }

    public static function editorOl(array $params): array
    {
        return [
            'content' => [
                'text' => $params['content']
            ],
            'type' => 'list'
        ];
    }

    public static function editorParagraph(array $params): array
    {
        return [
            'content' => [
                'text' => '<p>' . $params['content'] . '</p>'
            ],
            'type' => 'text'
        ];
    }

    public static function editorUl(array $params): array
    {
        return [
            'content' => [
                'text' => $params['content']
            ],
            'type' => 'list'
        ];
    }

    public static function editorVideo(array $params): array
    {
        return [
            'content' => [
                'caption' => $params['attrs']['caption'] ?? null,
                'url'     => $params['attrs']['src'] ?? null
            ],
            'type' => 'video'
        ];
    }

    public static function isEditorBlock(array $params): bool
    {
        if (isset($params['attrs']) === true) {
            return true;
        }

        if (is_string($params['content'] ?? null) === true) {
            return true;
        }

        return false;
    }
}
