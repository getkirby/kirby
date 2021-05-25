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
     * Breadcrumb array
     *
     * @return array
     */
    public function breadcrumb(): array
    {
        return [
            [
                'label' => $this->model->username(),
                'link'  => $this->url(true),
            ]
        ];
    }

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
     * @return \Kirby\Cms\File|\Kirby\Filesystem\Asset|null
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
        $params['text'] = $params['text'] ?? '{{ user.username }}';

        return array_merge(parent::pickerData($params), [
            'email'    => $this->model->email(),
            'username' => $this->model->username(),
        ]);
    }

    /**
     * Returns the data array for the
     * view's component props
     *
     * @internal
     *
     * @return array
     */
    public function props(): array
    {
        $user   = $this->model;
        $avatar = $user->avatar();

        return array_merge(parent::props(), [
            'user' => [
                'avatar'   => $avatar ? $avatar->url() : null,
                'content'  => $this->content(),
                'email'    => $user->email(),
                'id'       => $user->id(),
                'language' => $this->translation()->name(),
                'name'     => $user->name()->toString(),
                'role'     => $user->role()->title(),
                'username' => $user->username(),
            ],
            'next' => function () use ($user) {
                $next = $user->next();
                return $next ? $next->panel()->prevnext('username') : null;
            },
            'prev' => function () use ($user) {
                $prev = $user->prev();
                return $prev ? $prev->panel()->prevnext('username') : null;
            }
        ]);
    }

    /**
     * Returns the data array for
     * this model's Panel routes
     *
     * @internal
     *
     * @return array
     */
    public function route(): array
    {
        return [
            'component' => 'k-user-view',
            'props'     => $this->props(),
            'view'      => [
                'breadcrumb' => $this->breadcrumb(),
                'id'         => 'user',
                'title'      => $this->model->username(),
            ]
        ];
    }

    /**
     * Returns the Translation object
     * for the selected Panel language
     *
     * @return \Kirby\Cms\Translation
     */
    public function translation()
    {
        $kirby = $this->model->kirby();
        $lang  = $this->model->language();
        return $kirby->translation($lang) ?? $kirby->translation('en');
    }
}
