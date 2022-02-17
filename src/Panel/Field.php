<?php

namespace Kirby\Panel;

use Kirby\Cms\File;
use Kirby\Cms\Page;

/**
 * Provides common field prop definitions
 * for dialogs and other places
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
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
     * File position
     *
     * @param \Kirby\Cms\File
     * @param array $props
     * @return array
     */
    public static function filePosition(File $file, array $props = []): array
    {
        $index   = 0;
        $options = [];

        foreach ($file->siblings(false)->sorted() as $sibling) {
            $index++;

            $options[] = [
                'value' => $index,
                'text'  => $index
            ];

            $options[] = [
                'value'    => $sibling->id(),
                'text'     => $sibling->filename(),
                'disabled' => true
            ];
        }

        $index++;

        $options[] = [
            'value' => $index,
            'text'  => $index
        ];

        return array_merge([
            'label'   => t('file.sort'),
            'type'    => 'select',
            'empty'   => false,
            'options' => $options
        ], $props);
    }


    /**
     * @return array
     */
    public static function hidden(): array
    {
        return ['type' => 'hidden'];
    }

    /**
     * Page position
     *
     * @param \Kirby\Cms\Page
     * @param array $props
     * @return array
     */
    public static function pagePosition(Page $page, array $props = []): array
    {
        $index    = 0;
        $options  = [];
        $siblings = $page->parentModel()->children()->listed()->not($page);

        foreach ($siblings as $sibling) {
            $index++;

            $options[] = [
                'value' => $index,
                'text'  => $index
            ];

            $options[] = [
                'value'    => $sibling->id(),
                'text'     => $sibling->title()->value(),
                'disabled' => true
            ];
        }

        $index++;

        $options[] = [
            'value' => $index,
            'text'  => $index
        ];

        // if only one available option,
        // hide field when not in debug mode
        if (count($options) < 2) {
            return static::hidden();
        }

        return array_merge([
            'label'    => t('page.changeStatus.position'),
            'type'     => 'select',
            'empty'    => false,
            'options'  => $options,
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
        $kirby   = kirby();
        $user    = $kirby->user();
        $isAdmin = $user && $user->isAdmin();
        $roles   = [];

        foreach ($kirby->roles() as $role) {
            // exclude the admin role, if the user
            // is not allowed to change role to admin
            if ($role->name() === 'admin' && $isAdmin === false) {
                continue;
            }

            $roles[] = [
                'text'  => $role->title(),
                'info'  => $role->description() ?? t('role.description.placeholder'),
                'value' => $role->name()
            ];
        }

        return array_merge([
            'label'    => t('role'),
            'type'     => count($roles) <= 1 ? 'hidden' : 'radio',
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
     * @param array $blueprints
     * @param array $props
     * @return array
     */
    public static function template(?array $blueprints = [], ?array $props = []): array
    {
        $options = [];

        foreach ($blueprints as $blueprint) {
            $options[] = [
                'text'  => $blueprint['title'] ?? $blueprint['text']  ?? null,
                'value' => $blueprint['name']  ?? $blueprint['value'] ?? null,
            ];
        }

        return array_merge([
            'label'    => t('template'),
            'type'     => 'select',
            'empty'    => false,
            'options'  => $options,
            'icon'     => 'template',
            'disabled' => count($options) <= 1
        ], $props);
    }

    /**
     * @param array $props
     * @return array
     */
    public static function title(array $props = []): array
    {
        return array_merge([
            'label' => t('title'),
            'type'  => 'text',
            'icon'  => 'title',
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
        $translations = [];
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
