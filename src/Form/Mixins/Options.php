<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\OptionException;
use Kirby\Util\A;

trait Options
{

    protected $options;
    protected $query;

    protected function defaultOptions()
    {
        return [];
    }

    protected function defaultQuery()
    {
        return null;
    }

    public function options()
    {
        return $this->options;
    }

    protected function optionsFromQuery(): array
    {
        return [];
    }

    protected function optionsFromUrl(): array
    {
        return [];
    }

    public function query()
    {
        return $this->query;
    }

    protected function setOptions($options = [])
    {
        switch ($options) {
            case 'query':
                $options = $this->optionsFromQuery();
                break;
            case 'url':
                $options = $this->optionsFromUrl();
                break;
        }

        $this->options = [];

        foreach ($options as $key => $option) {
            if (is_array($option) === false || isset($option['value']) === false) {
                $option = [
                    'value' => $key,
                    'text'  => $option
                ];
            }

            // translate the option text
            $option['text'] = $this->i18n($option['text']);

            // add the option to the list
            $this->options[] = $option;
        }

        return $this;
    }

    protected function setQuery($query = null)
    {
        $this->query = $query;
        return $this;
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

