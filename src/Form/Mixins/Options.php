<?php

namespace Kirby\Form\Mixins;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Kirby\Form\Exceptions\OptionException;
use Kirby\Form\OptionsApi;
use Kirby\Form\OptionsQuery;
use Kirby\Util\A;

trait Options
{

    protected $options = [];
    protected $query = null;
    protected $url;

    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        switch ($this->options) {
            case 'query':
                return $this->options = $this->optionsFromQuery();
            case 'url':
                return $this->options = $this->optionsFromUrl();
        }

        $options = [];

        foreach ($this->options as $key => $option) {
            if (is_array($option) === false || isset($option['value']) === false) {
                $option = [
                    'value' => $key,
                    'text'  => $option
                ];
            }

            // translate the option text
            $option['text'] = $this->i18n($option['text']);

            // add the option to the list
            $options[] = $option;
        }

        return $this->options = $options;
    }

    protected function optionsDataAliases(): array
    {
        return [
            Page::class            => 'page',
            File::class            => 'file',
            User::class            => 'user',
            StructureObject::class => 'item'
        ];
    }

    protected function optionsData(): array
    {
        $model = $this->model();
        $kirby = $model->kirby();

        // default data setup
        $data = [
            'site'  => $kirby->site(),
            'users' => $kirby->users(),
        ];

        // add the model by the proper alias
        foreach ($this->optionsDataAliases() as $className => $alias) {
            if (is_a($model, $className) === true) {
                $data[$alias] = $model;
            }
        }

        return $data;
    }

    protected function optionsFromQuery(): array
    {
        $kirby = $this->model()->kirby();
        $query = $this->query();

        // default text setup
        $text = [
            'file' => '{{ file.filename }}',
            'page' => '{{ page.title }}',
            'user' => '{{ user.email }}',
            'item' => '{{ item.title }}'
        ];

        // default value setup
        $value = [
            'file' => '{{ file.id }}',
            'page' => '{{ page.id }}',
            'user' => '{{ user.id }}',
            'item' => '{{ item.id }}'
        ];

        // resolve array query setup
        if (is_array($query) === true) {
            $text  = $query['text']  ?? $text;
            $value = $query['value'] ?? $value;
            $query = $query['fetch'] ?? null;
        }

        $optionsQuery = new OptionsQuery([
            'aliases' => $this->optionsDataAliases(),
            'data'    => $this->optionsData(),
            'query'   => $query,
            'text'    => $text,
            'value'   => $value
        ]);

        return $optionsQuery->options();
    }

    protected function optionsFromUrl(): array
    {
        $kirby = $this->model()->kirby();
        $url   = $this->url();
        $fetch = null;
        $text  = null;
        $value = null;

        if (is_array($url) === true) {
            $fetch = $url['fetch'] ?? null;
            $text  = $url['text']  ?? null;
            $value = $url['value'] ?? null;
            $url   = $url['url']   ?? null;
        }

        $optionsApi = new OptionsApi([
            'data'  => $this->optionsData(),
            'fetch' => $fetch,
            'url'   => $url,
            'text'  => $text,
            'value' => $value
        ]);

        return $optionsApi->options();
    }

    public function query()
    {
        return $this->query;
    }

    protected function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    protected function setQuery($query = null)
    {
        $this->query = $query;
        return $this;
    }

    protected function setUrl($url = null)
    {
        $this->url = $url;
        return $this;
    }

    public function url()
    {
        return $this->url;
    }

    public function values(): array
    {
        return A::pluck($this->options(), 'value');
    }

    protected function validateSingleOption($value)
    {
        if ($this->isEmpty($value) === false) {
            if (in_array($value, $this->values(), true) !== true) {
                throw new OptionException();
            }
        }

        return true;
    }

    protected function validateMultipleOptions(array $value)
    {
        if ($this->isEmpty($value) === false) {

            $values = $this->values();

            foreach ($value as $key => $val) {
                if (in_array($val, $values, true) === false) {
                    throw new OptionException();
                }
            }

        }

        return true;
    }

}

