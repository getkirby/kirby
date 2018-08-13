<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Throwable;

abstract class ModelWithContent extends Model
{

    /**
     * The content
     *
     * @var Content
     */
    protected $content;

    protected $contentFile;

    protected $translations;

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
        if ($this->kirby()->multilang() === false) {
            try {
                $data = Data::read($this->contentFile());
            } catch (Throwable $e) {
                $data = [];
            }
        } else {
            $data = $this->translationData($languageCode);
        }

        return $this->setContent($data)->content();
    }

    /**
     * Needs to be declared by the final model
     *
     * @return string
     */
    abstract public function contentFile(): string;

    /**
     * Prepares the content that should be written
     * to the text file
     *
     * @return array
     */
    public function contentFileData(): array
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
        return $this->content()->get($field)->toDate($format);
    }

    /**
     * Returns all content validation errors
     *
     * @return array
     */
    public function errors(): array
    {
        $errors = [];

        foreach ($this->blueprint()->sections() as $section) {
            $errors = array_merge($errors, $section->errors());
        }

        return $errors;
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
     * Stores the content on disk
     *
     * @param string $languageCode
     * @return self
     */
    public function save(string $languageCode = null)
    {
        if ($this->kirby()->multilang() === true) {
            if ($language = $this->kirby()->language($languageCode)) {
                $languageCode = $language->code();
            } else {
                throw new InvalidArgumentException('Invalid language: ' . $languageCode);
            }
        } else {
            $languageCode = null;
        }

        Data::write($this->contentFile($languageCode), $this->contentFileData());
        return $this;
    }

    /**
     * Sets the Content object
     *
     * @param Content|null $content
     * @return self
     */
    protected function setContent(array $content = null)
    {
        if ($content !== null) {
            $content = new Content($content, $this);
        }

        $this->content = $content;
        return $this;
    }

    protected function setTranslations(array $translations = null)
    {
        if ($translations !== null) {
            $this->translations = new Collection;

            foreach ($translations as $props) {
                $props['parent'] = $this;
                $translation = new ContentTranslation($props);
                $this->translations->data[$translation->code()] = $translation;
            }
        }

        return $this;
    }

    /**
     * Fetch the content translation for the current language
     *
     * @param array
     * @return array
     */
    public function translationData(string $languageCode = null): array
    {
        $language = $this->kirby()->language($languageCode);

        if ($language === null) {
            throw new InvalidArgumentException('Invalid language: ' . $languageCode);
        }

        $translation = $this->translations()->find($language->code());
        $content     = $translation->content();

        // inject the default translation as fallback
        if ($language->isDefault() === false) {
            $defaultLanguage    = $this->kirby()->languages()->default();
            $defaultTranslation = $this->translations()->find($defaultLanguage->code());

            // fill missing content with the default translation
            $content = array_merge($defaultTranslation->content(), $content);
        }

        return $content;
    }

    public function translations()
    {
        if ($this->translations !== null) {
            return $this->translations;
        }

        $this->translations = new Collection;

        foreach ($this->kirby()->languages() as $language) {
            $translation = new ContentTranslation([
                'parent' => $this,
                'code'   => $language->code(),
            ]);

            $this->translations->data[$translation->code()] = $translation;
        }

        return $this->translations;
    }

    /**
     * Updates the model data
     *
     * @param array $input
     * @param string $language
     * @param boolean $validate
     * @return self
     */
    public function update(array $input = null, string $language = null, bool $validate = false)
    {
        $form = Form::for($this, [
            'values' => $input
        ]);

        // validate the input
        if ($validate === true) {
            if ($form->isInvalid() === true) {
                throw new InvalidArgumentException([
                    'fallback' => 'Invalid form with errors',
                    'details'  => $form->errors()
                ]);
            }
        }

        return $this->commit('update', [$this, $form->values(), $form->strings(), $language], function ($model, $values, $strings, $language) {
            return $model->clone(['content' => $strings])->save($language);
        });
    }
}
