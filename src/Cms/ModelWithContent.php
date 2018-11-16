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

        // single language support
        if ($this->kirby()->multilang() === false) {
            if (is_a($this->content, 'Kirby\Cms\Content') === true) {
                return $this->content;
            }

            try {
                $data = Data::read($this->contentFile());
            } catch (Throwable $e) {
                $data = [];
            }

            return $this->setContent($data)->content;

        // multi language support
        } else {

            // only fetch from cache for the default language
            if ($languageCode === null && is_a($this->content, 'Kirby\Cms\Content') === true) {
                return $this->content;
            }

            $data    = $this->translationData($languageCode);
            $content = new Content($data, $this);

            // only store the content for the default language
            if ($languageCode === null) {
                $this->content = $content;
            }

            return $content;
        }
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
     * @param array $data
     * @param string $languageCode
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
    {
        return $data;
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
            if (method_exists($section, 'errors') === true || isset($section->errors)) {
                $errors = array_merge($errors, $section->errors());
            }
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
     * @param array $data
     * @param bool $overwrite
     * @return self
     */
    public function save(array $data = null, string $languageCode = null, bool $overwrite = false)
    {
        if ($this->kirby()->multilang() === true) {
            return $this->saveTranslation($data, $languageCode, $overwrite);
        } else {
            return $this->saveContent($data, $overwrite);
        }
    }

    /**
     * Save the single language content
     *
     * @param array|null $data
     * @param bool $overwrite
     * @return self
     */
    protected function saveContent(array $data = null, bool $overwrite = false)
    {
        // create a clone to avoid modifying the original
        $clone = $this->clone();

        // merge the new data with the existing content
        $clone->content()->update($data, $overwrite);

        // send the full content array to the writer
        $clone->write($clone->content()->toArray());

        return $clone;
    }

    /**
     * Save a translation
     *
     * @param array|null $data
     * @param string|null $languageCode
     * @param bool $overwrite
     * @return self
     */
    protected function saveTranslation(array $data = null, string $languageCode = null, bool $overwrite = false)
    {
        // get the right language code
        $languageCode = $languageCode ?? $this->kirby()->language()->code();

        // create a clone to not touch the original
        $clone = $this->clone();

        // fetch the matching translation and update all the strings
        $translation = $clone->translations()->get($languageCode);

        if ($translation === null) {
            throw new InvalidArgument('The translation could not be found');
        }

        // merge the translation with the new data
        $translation->update($data, $overwrite);

        // send the full translation array to the writer
        $clone->write($translation->content(), $languageCode);

        // reset the content object
        $clone->content = null;

        // return the updated model
        return $clone;
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

    /**
     * Create the translations collection from an array
     *
     * @param array $translations
     * @return self
     */
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
            $defaultLanguage    = $this->kirby()->defaultLanguage();
            $defaultTranslation = $this->translations()->find($defaultLanguage->code());

            // fill missing content with the default translation
            $content = array_merge($defaultTranslation->content(), $content);
        }

        return $content;
    }

    /**
     * Returns the translations collection
     *
     * @return Collection
     */
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
    public function update(array $input = null, string $languageCode = null, bool $validate = false)
    {
        $form = Form::for($this, [
            'input' => $input
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

        return $this->commit('update', [$this, $form->data(), $form->strings(), $languageCode], function ($model, $values, $strings, $languageCode) {
            return $model->save($strings, $languageCode, true);
        });
    }

    /**
     * Low level data writer method
     * to store the given data on disk or anywhere else
     *
     * @param array $data
     * @param string $languageCode
     * @return boolean
     */
    protected function write(array $data, string $languageCode = null): bool
    {
        return Data::write(
            $this->contentFile($languageCode),
            $this->contentFileData($data, $languageCode)
        );
    }
}
