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
    public $content;

    /**
     * @var Translations
     */
    public $translations;

    /**
     * Returns the blueprint of the model
     *
     * @return Blueprint
     */
    abstract public function blueprint();

    /**
     * Executes any given model action
     *
     * @param string $action
     * @param array $arguments
     * @param Closure $callback
     * @return mixed
     */
    abstract protected function commit(string $action, array $arguments, Closure $callback);

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

            return $this->setContent($this->readContent())->content;

        // multi language support
        } else {

            // only fetch from cache for the default language
            if ($languageCode === null && is_a($this->content, 'Kirby\Cms\Content') === true) {
                return $this->content;
            }

            // get the translation by code
            if ($translation = $this->translation($languageCode)) {
                $content = new Content($translation->content(), $this);
            } else {
                throw new InvalidArgumentException('Invalid language: ' . $languageCode);
            }

            // only store the content for the current language
            if ($languageCode === null) {
                $this->content = $content;
            }

            return $content;
        }
    }

    /**
     * Returns the absolute path to the content file
     *
     * @internal
     * @param string|null $languageCode
     * @param bool $force
     * @return string
     */
    public function contentFile(string $languageCode = null, bool $force = false): string
    {
        $extension = $this->contentFileExtension();
        $directory = $this->contentFileDirectory();
        $filename  = $this->contentFileName();

        // overwrite the language code
        if ($force === true) {
            if (empty($languageCode) === false) {
                return $directory . '/' . $filename . '.' . $languageCode . '.' . $extension;
            } else {
                return $directory . '/' . $filename . '.' . $extension;
            }
        }

        // add and validate the language code in multi language mode
        if ($this->kirby()->multilang() === true) {
            if ($language = $this->kirby()->languageCode($languageCode)) {
                return $directory . '/' . $filename . '.' . $language . '.' . $extension;
            } else {
                throw new InvalidArgumentException('Invalid language: ' . $languageCode);
            }
        } else {
            return $directory . '/' . $filename . '.' . $extension;
        }
    }

    /**
     * Prepares the content that should be written
     * to the text file
     *
     * @internal
     * @param array $data
     * @param string $languageCode
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
    {
        return $data;
    }

    /**
     * Returns the absolute path to the
     * folder in which the content file is
     * located
     *
     * @internal
     * @return string|null
     */
    public function contentFileDirectory(): ?string
    {
        return $this->root();
    }

    /**
     * Returns the extension of the content file
     *
     * @internal
     * @return string
     */
    public function contentFileExtension(): string
    {
        return $this->kirby()->contentExtension();
    }

    /**
     * Needs to be declared by the final model
     *
     * @internal
     * @return string
     */
    abstract public function contentFileName(): string;

    /**
     * Decrement a given field value
     *
     * @param string $field
     * @param integer $by
     * @param integer $min
     * @return self
     */
    public function decrement(string $field, int $by = 1, int $min = 0)
    {
        $value = (int)$this->content()->get($field)->value() - $by;

        if ($value < $min) {
            $value = $min;
        }

        return $this->update([$field => $value]);
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
     * Increment a given field value
     *
     * @param string $field
     * @param integer $by
     * @param integer $max
     * @return self
     */
    public function increment(string $field, int $by = 1, int $max = null)
    {
        $value = (int)$this->content()->get($field)->value() + $by;

        if ($max && $value > $max) {
            $value = $max;
        }

        return $this->update([$field => $value]);
    }

    /**
     * Checks if the data has any errors
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        return Form::for($this)->hasErrors() === false;
    }

    /**
     * Read the content from the content file
     *
     * @internal
     * @param string|null $languageCode
     * @return array
     */
    public function readContent(string $languageCode = null): array
    {
        try {
            return Data::read($this->contentFile($languageCode));
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Returns the absolute path to the model
     *
     * @return string
     */
    abstract public function root(): ?string;

    /**
     * Stores the content on disk
     *
     * @internal
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
        $clone->writeContent($clone->content()->toArray());

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
        // create a clone to not touch the original
        $clone = $this->clone();

        // fetch the matching translation and update all the strings
        $translation = $clone->translation($languageCode);

        if ($translation === null) {
            throw new InvalidArgumentException('Invalid language: ' . $languageCode);
        }

        // merge the translation with the new data
        $translation->update($data, $overwrite);

        // send the full translation array to the writer
        $clone->writeContent($translation->content(), $languageCode);

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
     * Returns a single translation by language code
     * If no code is specified the current translation is returned
     *
     * @param string $languageCode
     * @return Translation|null
     */
    public function translation(string $languageCode = null)
    {
        return $this->translations()->find($languageCode ?? $this->kirby()->language()->code());
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
            'input'          => $input,
            'ignoreDisabled' => $validate === false,
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
     * @internal
     * @param array $data
     * @param string $languageCode
     * @return boolean
     */
    public function writeContent(array $data, string $languageCode = null): bool
    {
        return Data::write(
            $this->contentFile($languageCode),
            $this->contentFileData($data, $languageCode)
        );
    }
}
