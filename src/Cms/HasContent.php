<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Throwable;

trait HasContent
{

    /**
     * The content
     *
     * @var Content
     */
    protected $content;

    /**
     * Modified getter to also return fields
     * from the object's content
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        // public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        return $this->content()->get($method, $arguments);
    }

    /**
     * Prepares the content object for the
     * toArray method
     *
     * @return array
     */
    protected function convertContentToArray(): array
    {
        return $this->content()->toArray();
    }

    /**
     * Returns the content
     *
     * @param string $languageCode
     * @return Content
     */
    public function content(string $languageCode = null): Content
    {
        if (is_a($this->content, 'Kirby\Cms\Content') === true) {
            return $this->content;
        }

        // single language support
        if (empty($this->kirby()->option('languages')) === true) {
            try {
                $data = Data::read($this->contentFile());
            } catch (Throwable $e) {
                $data = [];
            }

            return $this->setContent($data)->content();
        }

        $language    = $this->kirby()->language($languageCode);
        $translation = $this->translations()->find($language->code());
        $content     = $translation->content();

        // inject the default translation as fallback
        if ($language->isDefault() === false) {
            $defaultLanguage    = $this->kirby()->languages()->default();
            $defaultTranslation = $this->translations()->find($defaultLanguage->code());

            // fill missing content with the default translation
            $content = array_merge($defaultTranslation->content(), $content);
        }

        return $this->setContent($content)->content();
    }

    /**
     * Returns a formatted date field from the content
     *
     * @param string $format
     * @param string $field
     * @return Field
     */
    public function date(string $format = null, $field = 'date')
    {
        return $this->content()->get($field)->toDate($format);
    }

    /**
     * Checks if the model data has any errors
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        return Form::for($this)->hasErrors() === false;
    }

    /**
     * Sets the Content object
     *
     * @param Content|null $content
     * @return self
     */
    protected function setContent(array $content = null): self
    {
        if ($content !== null) {
            $content = new Content($content, $this);
        }

        $this->content = $content;
        return $this;
    }
}
