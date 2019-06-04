<?php

namespace Kirby\Cms;

/**
 * HasPanelImage
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait HasPanelImage
{

    /**
     * @internal
     * @param string|array|false $settings
     * @return array|null
     */
    public function panelImage($settings = null): ?array
    {
        $defaults = [
            'ratio' => '3/2',
            'back'  => 'pattern',
            'cover' => false
        ];

        // switch the image off
        if ($settings === false) {
            return null;
        }

        if (is_string($settings) === true) {
            $settings = [
                'query' => $settings
            ];
        }

        if ($image = $this->panelImageSource($settings['query'] ?? null)) {

            // for cards
            $settings['cards'] = [
                'url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw',
                'srcset' => $image->srcset([
                    352,
                    432,
                    704,
                    864,
                    944,
                    1056,
                    1408,
                    1888,
                    2112,
                    2816
                ])
            ];

            // for lists
            $settings['list'] = [
                'url' => $image->thumb([
                    'width' => 38,
                    'height' => 38,
                    'crop' => 'center'
                ])->url(true),
                'srcset' => $image->srcset([
                    '1x' => [
                        'width' => 38,
                        'height' => 38,
                        'crop' => 'center'
                    ],
                    '2x' => [
                        'width' => 76,
                        'height' => 76,
                        'crop' => 'center'
                    ],
                    '3x' => [
                        'width' => 152,
                        'height' => 152,
                        'crop' => 'center'
                    ]
                ])
            ];

            unset($settings['query']);
        }

        return array_merge($defaults, (array)$settings);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return Kirby\Cms\File|Kirby\Cms\Asset|null
     */
    protected function panelImageSource(string $query = null)
    {
        // define default for pages
        if (is_a($this, Page::class) === true) {
            $default = 'page.image';
        }

        $image = $this->query($query ?? $default ?? null);

        // validate the query result
        if (is_a($image, File::class) === false && is_a($image, Asset::class) === false) {
            $image = null;
        }

        // fallback for files
        if ($image === null && is_a($this, File::class) === true && $this->isViewable() === true) {
            $image = $this;
        }

        return $image;
    }
}
