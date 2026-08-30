<?php

namespace Kirby\Guards;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Model;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\File as Upload;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Validators for the input of `$file` actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FileValidators extends ModelValidators
{
	/**
	 * @var File
	 */
	protected Model $model;

	/**
	 * Validates the input of the `changeName` action
	 */
	protected function ensureToChangeName(string $name): void
	{
		$this->validateName($name);
		$this->validateDuplicate($name);
	}

	/**
	 * Validates the input of the `changeTemplate` action
	 */
	protected function ensureToChangeTemplate(string $template): void
	{
		$this->validateTemplate($template);
	}

	/**
	 * Validates the file that is about to be created
	 */
	protected function ensureToCreate(Upload $upload): void
	{
		$this->validateDoesNotExist();
		$this->validateUpload($upload);
	}

	/**
	 * Validates the input of the `replace` action
	 */
	protected function ensureToReplace(Upload $upload): void
	{
		$this->validateUploadWithSameType($upload);
	}

	/**
	 * Validates that the file does not exist yet
	 */
	public function validateDoesNotExist(): void
	{
		if ($this->model->exists() === true) {
			throw new DuplicateException(
				key: 'file.duplicate',
				data: ['filename' => $this->model->filename()]
			);
		}
	}

	/**
	 * Validates that no other file with the same name exists
	 */
	public function validateDuplicate(string $name): void
	{
		$duplicate = $this->model->parent()->files()
			->not($this->model)
			->findBy('filename', $name . '.' . $this->model->extension());

		if ($duplicate !== null) {
			throw new DuplicateException(
				key: 'file.duplicate',
				data: ['filename' => $duplicate->filename()]
			);
		}
	}

	/**
	 * Validates the file extension
	 */
	public function validateExtension(string $extension): void
	{
		// make it easier to compare the extension
		$extension = strtolower($extension);

		if ($extension === '') {
			$this->error(
				key: 'file.extension.missing',
				data: ['filename' => $this->model->filename()]
			);
		}

		if (
			Str::contains($extension, 'php') === true ||
			Str::contains($extension, 'phar') === true ||
			Str::contains($extension, 'pht') === true
		) {
			$this->error(
				key: 'file.type.forbidden',
				data: ['type' => 'PHP']
			);
		}

		if (Str::contains($extension, 'htm') === true) {
			$this->error(
				key: 'file.type.forbidden',
				data: ['type' => 'HTML']
			);
		}

		if (V::in($extension, ['exe', App::instance()->contentExtension()]) === true) {
			$this->error(
				key: 'file.extension.forbidden',
				data: ['extension' => $extension]
			);
		}
	}

	/**
	 * Validates the extension, MIME type and filename
	 *
	 * @param $mime If not passed, the MIME type is detected from the file,
	 *             if `false`, the MIME type is not validated for performance reasons
	 */
	public function validateFile(string|false|null $mime = null): void
	{
		// request to skip the MIME check for performance reasons
		if ($mime !== false) {
			$this->validateMime($mime ?? $this->model->mime());
		}

		$this->validateExtension($this->model->extension());
		$this->validateFilename($this->model->filename());
	}

	/**
	 * Validates the filename
	 */
	public function validateFilename(string $filename): void
	{
		// make it easier to compare the filename
		$filename = strtolower($filename);

		// check for missing filenames
		if ($filename === '') {
			$this->error(
				key: 'file.name.missing'
			);
		}

		// Block htaccess files
		if (Str::startsWith($filename, '.ht') === true) {
			$this->error(
				key: 'file.type.forbidden',
				data: ['type' => 'Apache config']
			);
		}

		// Block invisible files
		if (Str::startsWith($filename, '.') === true) {
			$this->error(
				key: 'file.type.forbidden',
				data: ['type' => 'invisible']
			);
		}
	}

	/**
	 * Validates the MIME type
	 */
	public function validateMime(string|null $mime = null): void
	{
		// make it easier to compare the mime
		$mime = strtolower($mime ?? '');

		if ($mime === '') {
			$this->error(
				key: 'file.mime.missing',
				data: ['filename' => $this->model->filename()]
			);
		}

		if (Str::contains($mime, 'php') === true) {
			$this->error(
				key: 'file.type.forbidden',
				data: ['type' => 'PHP']
			);
		}

		if (V::in($mime, ['text/html', 'application/x-msdownload']) === true) {
			$this->error(
				key: 'file.mime.forbidden',
				data: ['mime' => $mime]
			);
		}
	}

	/**
	 * Validates if the filename can be changed to the given name
	 */
	public function validateName(string $name): void
	{
		if (Str::length($name) === 0) {
			$this->error(
				key: 'file.changeName.empty'
			);
		}
	}

	/**
	 * Validates that the given template is a valid blueprint
	 * option for this file
	 */
	public function validateTemplate(string $template): void
	{
		$blueprints = $this->model->blueprints();

		if (in_array($template, array_column($blueprints, 'name'), true) === false) {
			$this->error(
				key: 'file.changeTemplate.invalid',
				data: [
					'id'         => $this->model->id(),
					'template'   => $template,
					'blueprints' => implode(', ', array_column($blueprints, 'name'))
				]
			);
		}
	}

	/**
	 * Validates the uploaded file against the general file rules
	 * as well as the `accept` settings of the file blueprint
	 */
	public function validateUpload(Upload $upload): void
	{
		$this->validateFile($upload->mime());

		$upload->match($this->model->blueprint()->accept());
		$upload->validateContents(true);
	}

	/**
	 * Validates an uploaded replacement file, which must match
	 * the MIME type or extension of the file it replaces
	 */
	public function validateUploadWithSameType(Upload $upload): void
	{
		$this->validateMime($upload->mime());

		if (
			$upload->mime() !== $this->model->mime() &&
			$upload->extension() !== $this->model->extension()
		) {
			$this->error(
				key: 'file.mime.differs',
				data: ['mime' => $this->model->mime()]
			);
		}

		$this->validateUpload($upload);
	}
}
