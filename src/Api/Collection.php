<?php

namespace Kirby\Api;

use Exception;
use Kirby\Toolkit\Str;

/**
 * The Collection class is a wrapper
 * around our Kirby Collections and handles
 * stuff like pagination and proper JSON output
 * for collections in REST calls.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Collection
{
    /**
     * @var \Kirby\Api\Api
     */
    protected $api;

    /**
     * @var mixed|null
     */
    protected $data;

    /**
     * @var mixed|null
     */
    protected $model;

    /**
     * @var mixed|null
     */
    protected $select;

    /**
     * @var mixed|null
     */
    protected $view;

    /**
     * Collection constructor
     *
     * @param \Kirby\Api\Api $api
     * @param mixed|null $data
     * @param array $schema
     * @throws \Exception
     */
    public function __construct(Api $api, $data, array $schema)
    {
        $this->api   = $api;
        $this->data  = $data;
        $this->model = $schema['model'] ?? null;
        $this->view  = $schema['view'] ?? null;

        if ($data === null) {
            if (is_a($schema['default'] ?? null, 'Closure') === false) {
                throw new Exception('Missing collection data');
            }

            $this->data = $schema['default']->call($this->api);
        }

        if (
            isset($schema['type']) === true &&
            is_a($this->data, $schema['type']) === false
        ) {
            throw new Exception('Invalid collection type');
        }
    }

    /**
     * @param string|array|null $keys
     * @return $this
     * @throws \Exception
     */
    public function select($keys = null)
    {
        if ($keys === false) {
            return $this;
        }

        if (is_string($keys)) {
            $keys = Str::split($keys);
        }

        if ($keys !== null && is_array($keys) === false) {
            throw new Exception('Invalid select keys');
        }

        $this->select = $keys;
        return $this;
    }

    /**
     * @return array
     * @throws \Kirby\Exception\NotFoundException
     * @throws \Exception
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->data as $item) {
            $model = $this->api->model($this->model, $item);

            if ($this->view !== null) {
                $model = $model->view($this->view);
            }

            if ($this->select !== null) {
                $model = $model->select($this->select);
            }

            $result[] = $model->toArray();
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Kirby\Exception\NotFoundException
     * @throws \Exception
     */
    public function toResponse(): array
    {
        if ($query = $this->api->requestQuery('query')) {
            $this->data = $this->data->query($query);
        }

        if (!$this->data->pagination()) {
            $this->data = $this->data->paginate([
                'page'  => $this->api->requestQuery('page', 1),
                'limit' => $this->api->requestQuery('limit', 100)
            ]);
        }

        $pagination = $this->data->pagination();

        if ($select = $this->api->requestQuery('select')) {
            $this->select($select);
        }

        if ($view = $this->api->requestQuery('view')) {
            $this->view($view);
        }

        return [
            'code'       => 200,
            'data'       => $this->toArray(),
            'pagination' => [
                'page'   => $pagination->page(),
                'total'  => $pagination->total(),
                'offset' => $pagination->offset(),
                'limit'  => $pagination->limit(),
            ],
            'status' => 'ok',
            'type'   => 'collection'
        ];
    }

    /**
     * @param string $view
     * @return $this
     */
    public function view(string $view)
    {
        $this->view = $view;
        return $this;
    }
}
