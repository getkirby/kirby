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

    /**
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $fetch;

    /**
     * @var
     */
    protected $options;

    /**
     * @var string
     */
    protected $text = '{{ item.value }}';

    /**
     * @var
     */
    protected $url;

    /**
     * @var string
     */
    protected $value = '{{ item.key }}';

    /**
     * OptionsApi constructor
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        return $this->fetch;
    }

    /**
     * @param string $field
     * @param array $data
     * @return string
     */
    protected function field(string $field, array $data)
    {
        $value = $this->$field();
        return Str::template($value, $data);
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Kirby\Exception\InvalidArgumentException
     */
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

    /**
     * @param array $data
     * @return self
     */
    protected function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string|null $fetch
     * @return self
     */
    protected function setFetch(string $fetch = null)
    {
        $this->fetch = $fetch;
        return $this;
    }

    /**
     * @param $options
     * @return self
     */
    protected function setOptions($options = null)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param $text
     * @return self
     */
    protected function setText($text = null)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param $url
     * @return self
     */
    protected function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param null $value
     * @return self
     */
    protected function setValue($value = null)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function text()
    {
        return $this->text;
    }

    /**
     * @return array
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function toArray(): array
    {
        return $this->options();
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return Str::template($this->url, $this->data());
    }

    /**
     * @return string
     */
    public function value()
    {
        return $this->value;
    }
}
