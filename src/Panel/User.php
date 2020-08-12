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
     * Returns the Panel icon type
     *
     * @return string
     */
    protected function imageIcon(): string
    {
        return 'user';
    }

    /**
     * Default settings for Panel image
     *
     * @return array
     */
    public function imageDefaults(): array
    {
        return [
            'back'  => 'black',
            'cover' => false,
            'ratio' => '1/1',
        ];
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

        return array_merge(
            parent::props(),
            $this->prevNext(),
            [
                'model' => [
                    'avatar'   => $avatar ? $avatar->url() : null,
                    'content'  => $this->content(),
                    'email'    => $user->email(),
                    'id'       => $user->id(),
                    'language' => $this->translation()->name(),
                    'name'     => $user->name()->toString(),
                    'role'     => $user->role()->title(),
                    'username' => $user->username(),
                ]
            ]
        );
    }

    /**
     * Returns navigation array with
     * previous and next user
     *
     * @internal
     *
     * @return array
     */
    public function prevNext(): array
    {
        $user = $this->model;

        return [
            'next' => function () use ($user) {
                $next = $user->next();
                return $next ? $next->panel()->toLink('username') : null;
            },
            'prev' => function () use ($user) {
                $prev = $user->prev();
                return $prev ? $prev->panel()->toLink('username') : null;
            }
        ];
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
            'breadcrumb' => $this->breadcrumb(),
            'component'  => 'k-user-view',
            'props'      => $this->props(),
            'title'      => $this->model->username(),
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
        return $kirby->translation($lang);
    }
}
