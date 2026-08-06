<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\Model;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * Validators for the input of `$language` actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguageValidators extends ModelValidators
{
	/**
	 * @var Language
	 */
	protected Model $model;

	/**
	 * Validates the language that is about to be created
	 */
	protected function ensureToCreate(): void
	{
		$this->validateCode($this->model->code());
		$this->validateName($this->model->name());
		$this->validateDoesNotExist();
	}

	/**
	 * Validates the input of the `update` action
	 *
	 * @param Language|null $oldLanguage The state before the update
	 */
	protected function ensureToUpdate(Language|null $oldLanguage = null): void
	{
		$this->validateCode($this->model->code());
		$this->validateName($this->model->name());
		$this->validateDemotion($oldLanguage);
	}

	/**
	 * Validates if the language code is formatted correctly
	 */
	public function validateCode(string $code): void
	{
		if (Str::length($code) < 2) {
			$this->error(
				key: 'language.code',
				data: [
					'code' => $code,
					'name' => $this->model->name()
				]
			);
		}
	}

	/**
	 * Validates that another language has already been set as
	 * default before the given language gets demoted
	 */
	public function validateDemotion(Language|null $oldLanguage = null): void
	{
		if (
			$oldLanguage !== null &&
			$oldLanguage->isDefault() === true &&
			$this->model->isDefault() === false &&
			$this->model->kirby()->defaultLanguage()->code() === $oldLanguage->code()
		) {
			throw new LogicException(
				message: 'Please select another language to be the primary language'
			);
		}
	}

	/**
	 * Validates that the language does not exist yet
	 */
	public function validateDoesNotExist(): void
	{
		if ($this->model->exists() === true) {
			throw new DuplicateException(
				key: 'language.duplicate',
				data: ['code' => $this->model->code()]
			);
		}
	}

	/**
	 * Validates if the language name is formatted correctly
	 */
	public function validateName(string $name): void
	{
		if (Str::length($name) < 1) {
			$this->error(
				key: 'language.name',
				data: [
					'code' => $this->model->code(),
					'name' => $name
				]
			);
		}
	}
}
