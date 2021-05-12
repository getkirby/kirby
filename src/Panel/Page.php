<?php

namespace Kirby\Panel;

/**
 * Provides information about the page model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Page extends Model
{
    /**
     * Provides a kirbytag or markdown
     * tag for the page, which will be
     * used in the panel, when the page
     * gets dragged onto a textarea
     *
     * @internal
     * @param string|null $type (null|auto|kirbytext|markdown)
     * @return string
     */
    public function dragText(string $type = null): string
    {
        $type = $this->dragTextType($type);

        if ($callback = $this->dragTextFromCallback($type)) {
            return $callback;
        }

        if ($type === 'markdown') {
            return '[' . $this->model->title() . '](' . $this->model->url() . ')';
        }

        return '(link: ' . $this->model->id() . ' text: ' . $this->model->title() . ')';
    }


    /**
     * Returns the Panel icon definition
     * according to the blueprint settings
     *
     * @param array|null $params
     * @return array
     */
    public function icon(array $params = null): array
    {
        if ($icon = $this->model->blueprint()->icon()) {
            $params['type'] = $icon;
        }

        return parent::icon($params);
    }

    /**
     * Returns the escaped Id, which is
     * used in the panel to make routing work properly
     *
     * @return string
     */
    public function id(): string
    {
        return str_replace('/', '+', $this->model->id());
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Cms\Asset|null
     */
    protected function imageSource(string $query = null)
    {
        if ($query === null) {
            $query = 'page.image';
        }

        return parent::imageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function path(): string
    {
        return 'pages/' . $this->id();
    }

    /**
     * Prepares the response data for page pickers
     * and page fields
     *
     * @param array|null $params
     * @return array
     */
    public function pickerData(array $params = []): array
    {
        return [
            'dragText'    => $this->dragText(),
            'hasChildren' => $this->model->hasChildren(),
            'image'       => $image = $this->image($params['image'] ?? []),
            'icon'        => $this->icon($image),
            'id'          => $this->model->id(),
            'info'        => $this->model->toString($params['info'] ?? false),
            'link'        => $this->url(true),
            'text'        => $this->model->toString($params['text'] ?? '{{ page.title }}'),
            'url'         => $this->model->url(),
        ];
    }
}
