<?php

namespace Kirby\Form;

use Kirby\Cms\Nest;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

/**
 * The OptionsApi class handles fetching options
 * from any REST API with valid JSON data.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsApi
{
    use Properties;

    protected $data;
    protected $fetch;
    protected $options;
    protected $text = '{{ item.value }}';
    protected $url;
    protected $value = '{{ item.key }}';

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public function data(): array
    {
        return $this->data;
    }

    public function fetch()
    {
        return $this->fetch;
    }

    protected function field(string $field, array $data)
    {
        $value = $this->$field();
        return Str::template($value, $data);
    }

    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        if (Url::isAbsolute($this->url()) === true) {
            // URL, request via cURL
            $data = Remote::get($this->url())->json();
        } else {
            // local file, get contents locally

            // ensure the file exists before trying to load it as the
            // file_get_contents() warnings need to be suppressed
            if (is_file($this->url()) !== true) {
                throw new Exception('Local file ' . $this->url() . ' was not found');
            }

            $content = @file_get_contents($this->url());

            if (is_string($content) !== true) {
                throw new Exception('Unexpected read error'); // @codeCoverageIgnore
            }

            if (empty($content) === true) {
                return [];
            }

            $data = json_decode($content, true);
        }

        if (is_array($data) === false) {
            throw new InvalidArgumentException('Invalid options format');
        }

        $result  = (new Query($this->fetch(), Nest::create($data)))->result();
        $options = [];

        foreach ($result as $item) {
            $data = array_merge($this->data(), ['item' => $item]);

            $options[] = [
                'text'  => $this->field('text', $data),
                'value' => $this->field('value', $data),
            ];
        }

        return $options;
    }

    protected function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    protected function setFetch(string $fetch = null)
    {
        $this->fetch = $fetch;
        return $this;
    }

    protected function setText($text = null)
    {
        $this->text = $text;
        return $this;
    }

    protected function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    protected function setValue($value = null)
    {
        $this->value = $value;
        return $this;
    }

    public function text()
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return $this->options();
    }

    public function url(): string
    {
        return Str::template($this->url, $this->data());
    }

    public function value()
    {
        return $this->value;
    }
}
