<?php

namespace Kirby\Panel;

/**
 * Provides information about the user model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class User extends Model
{
    /**
     * Panel icon definition
     *
     * @param array $params
     * @return array
     */
    public function icon(array $params = null): array
    {
        $params['type'] = 'user';
        return parent::icon($params);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Cms\Asset|null
     */
    protected function imageSource(string $query = null)
    {
        if ($query === null) {
            return $this->model->avatar();
        }

        return parent::imageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    public function path(): string
    {
        return 'users/' . $this->model->id();
    }

    /**
     * Returns prepared data for the panel user picker
     *
     * @param array|null $params
     * @return array
     */
    public function pickerData(array $params = null): array
    {
        return [
            'id'       => $this->model->id(),
            'image'    => $image = $this->image($params['image'] ?? []),
            'icon'     => $this->icon($image),
            'email'    => $this->model->email(),
            'info'     => $this->model->toString($params['info'] ?? false),
            'link'     => $this->url(true),
            'text'     => $this->model->toString($params['text'] ?? '{{ user.username }}'),
            'username' => $this->model->username(),
        ];
    }
}
