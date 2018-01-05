<?php

namespace Kirby\Cms;

/**
 * The StructureObject reprents each item
 * in a Structure collection. StructureObjects
 * behave pretty much the same as Pages or Users
 * and have a Content object to access their fields.
 * All fields in a StructureObject are therefor also
 * wrapped in a Field object and can be accessed in
 * the same way as Page fields. They also use the same
 * Field methods.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class StructureObject extends Object
{

    use HasContent;
    use HasSiblings;

    /**
     * Property schema
     *
     * @return array
     */
    protected function schema()
    {
        return [
            'id' => [
                'type'     => 'string',
                'required' => true,
            ],
            'collection' => [
                'type' => Structure::class
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function () {
                    return new Content([], $this->parent());
                }
            ],
            'parent' => [
                'type' => Object::class,
            ],
        ];
    }

    /**
     * Converts all fields in the object to a
     * plain associative array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->content()->toArray();
    }

}
