<?php

namespace Kirby\Cms;

use Exception;

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
        return $this->content()->get($method, ...$arguments);
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
     * Returns a formatted date field from the content
     *
     * @param string $format
     * @param string $field
     * @return Field
     */
    public function date(string $format = null, $field = 'date')
    {
        return $this->content()->get($field, [$format]);
    }

    /**
     * Returns the content
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        return $this->content = new Content([]);
    }

    /**
     * Sets the Content object
     *
     * @param Content|null $content
     * @return self
     */
    protected function setContent(Content $content = null): self
    {
        $this->content = $content;
        $this->content->setParent($this);
        return $this;
    }

}
