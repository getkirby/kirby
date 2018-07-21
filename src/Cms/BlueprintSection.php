<?php

namespace Kirby\Cms;

use Kirby\Api\Api;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

/**
 * Foundation for all blueprint sections
 * (BlueprintPagesSection, BlueprintFilesSection, etc.)
 */
class BlueprintSection extends BlueprintObject
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * Creates a new BlueprintSection object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $props = Blueprint::extend($props);
        $this->setProperties($props);
    }

    public function api(Api $parentApi)
    {
        return $parentApi->clone([
            'routes' => $this->routes(),
            'data'   => array_merge($parentApi->data(), ['section' => $this])
        ]);
    }

    public function apiCall(Api $parentApi, string $path = '')
    {
        return $this->api($parentApi)->call($path, $parentApi->requestMethod(), $parentApi->requestData());
    }

    /**
     * General factory for any section type
     *
     * @param array $props
     * @return BlueprintSection
     */
    public static function factory(array $props)
    {
        if (isset($props['type']) === false) {
            throw new InvalidArgumentException([
                'key' => 'blueprint.section.type.missing',
            ]);
        }

        $className = __NAMESPACE__ . '\\Blueprint' . ucfirst($props['type']) . 'Section';

        if (class_exists($className) === false) {
            throw new InvalidArgumentException([
                'key'  => 'blueprint.section.type.invalid',
                'data' => ['type' => $props['type']]
            ]);
        }

        return new $className($props);
    }

    /**
     * Gets the value of id
     * Will fall back to the name
     *
     * @return string
     */
    public function id(): string
    {
        return $this->name();
    }

    /**
     * Returns the simple name of the model type
     *
     * @param object|null $model
     * @return string
     */
    public function modelType($model = null): string
    {
        $model = $model ?? $this->model();
        $types = [
            'page' => 'Kirby\Cms\Page',
            'site' => 'Kirby\Cms\Site',
            'file' => 'Kirby\Cms\File',
            'user' => 'Kirby\Cms\User'
        ];

        foreach ($types as $type => $className) {
            if (is_a($model, $className) === true) {
                return $type;
            }
        }

        throw new InvalidArgumentException('The model type "' . get_class($model) . '" is not supported');
    }

    /**
     * Gets the value of name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function routes(): array
    {
        return [
            'read' => [
                'pattern' => '/',
                'method'  => 'GET',
                'action'  => function () {
                    return $this->section()->toArray();
                }
            ]
        ];
    }

    public function stringQuery(string $query, array $data = [])
    {
        return (new Query($query, $this->stringQueryData($data)))->result();
    }

    public function stringQueryData($data = []): array
    {
        $model = $this->model();

        if ($model === null) {
            throw new InvalidArgumentException('The section model is missing');
        }

        $defaults = [
            'site'  => $model->site(),
            'kirby' => $model->kirby(),
        ];

        // inject the model with the simple model name
        $defaults[$this->modelType()] = $model;

        return array_merge($defaults, $data);
    }

    public function stringTemplate(string $template = null, array $data = [])
    {
        return Str::template($template, $this->stringQueryData($data));
    }

    /**
     * Sets the value of name
     *
     * @param string $name
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Sets the value of type
     *
     * @param string $type
     * @return  self
     */
    protected function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Converts the section object to a handy
     * array i.e. for API results
     *
     * @return array
     */
    public function toArray(): array
    {
        $props   = $this->propertiesToArray();
        $options = $props;

        unset($options['data']);
        unset($options['pagination']);

        return [
            'code'       => 200,
            'data'       => $props['data'],
            'options'    => $options,
            'pagination' => $props['pagination'],
            'status'     => 'ok',
            'type'       => 'section'
        ];
    }

    /**
     * Gets the value of type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function toLayout()
    {
        return $this->id();
    }
}
