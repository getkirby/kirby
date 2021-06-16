<?php

namespace Kirby\Panel;

/**
 * Provides common field prop definitons
 * for dialogs and other places
 *
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Field
{
    /**
     * A standard email field
     *
     * @param array $props
     * @return array
     */
    public static function email(array $props = []): array
    {
        return array_merge([
            'label'   => t('email'),
            'type'    => 'email',
            'counter' => false,
        ], $props);
    }

    /**
     * A regular password field
     *
     * @param array $props
     * @return array
     */
    public static function password(array $props = []): array
    {
        return array_merge([
            'label' => t('password'),
            'type'  => 'password'
        ], $props);
    }

    /**
     * User role radio buttons
     *
     * @param array $props
     * @return array
     */
    public static function role(array $props = []): array
    {
        // TODO: exclude the admin role, if the user
        // is not allowed to change role to admin
        foreach (kirby()->roles() as $role) {
            $roles[] = [
                'text'  => $role->title(),
                'info'  => $role->description() ?? t('role.description.placeholder'),
                'value' => $role->name()
            ];
        }

        return array_merge([
            'label'    => t('role'),
            'type'     => count($roles) <= 1 ? 'hidden' : 'radio',
            'required' => true,
            'options'  => $roles
        ], $props);
    }

    /**
     * @param array $props
     * @return array
     */
    public static function slug(array $props = []): array
    {
        return array_merge([
            'label' => t('slug'),
            'type'  => 'slug',
        ], $props);
    }

    /**
     * @param array $props
     * @return array
     */
    public static function title(array $props = []): array
    {
        return array_merge([
            'label'     => t('title'),
            'type'      => 'text',
            'icon'      => 'title',
            'required'  => true,
            'preselect' => true
        ], $props);
    }

    /**
     * Panel translation select box
     *
     * @param array $props
     * @return array
     */
    public static function translation(array $props = []): array
    {
        foreach (kirby()->translations() as $translation) {
            $translations[] = [
                'text'  => $translation->name(),
                'value' => $translation->code()
            ];
        }

        return array_merge([
            'label'    => t('language'),
            'type'     => 'select',
            'icon'     => 'globe',
            'options'  => $translations,
            'required' => true,
            'empty'    => false
        ], $props);
    }

    /**
     * @param array $props
     * @return array
     */
    public static function username(array $props = []): array
    {
        return array_merge([
            'icon'  => 'user',
            'label' => t('name'),
            'type'  => 'text',
        ], $props);
    }
}
