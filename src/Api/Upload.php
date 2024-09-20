<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\FileRules;
use Kirby\Cms\Page;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * The Upload class handles file uploads in the
 * context of the API. It adds support for chunked
 * uploads.
 *
 * @package   Kirby Api
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
readonly class Upload
{
	public function __construct(
		protected Api $api,
		protected bool $single = true,
		protected bool $debug = false
	) {
	}

	/**
	 * Ensures a clean chunk ID by stripping forbidden characters
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException Too short ID string
	 */
	public static function chunkId(string $id): string
	{
		$id = Str::slug($id, '', 'a-z0-9');

		if (strlen($id) < 3) {
			throw new InvalidArgumentException(
				message: 'Chunk ID must at least be 3 characters long'
			);
		}

		return $id;
	}

	/**
	 * Returns the ideal size for a file chunk
	 */
	public static function chunkSize(): int
	{
		$max = [
			Str::toBytes(ini_get('upload_max_filesize')),
			Str::toBytes(ini_get('post_max_size'))
		];

		// consider cloudflare proxy limit, if detected
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) === true) {
			$max[] = Str::toBytes('100M');
		}

		// to be sure, only use 95% of the max possible upload size
		return (int)floor(min($max) * 0.95);
	}

	/**
	 * Clean up tmp directory of stale files
	 */
	public static function cleanTmpDir(): void
	{
		foreach (Dir::files($dir = static::tmpDir(), [], true) as $file) {
			// remove any file that hasn't been altered
			// in the last 24 hours
			if (F::modified($file) < time() - 86400) {
				F::remove($file);
			}
		}

		// remove tmp directory if completely empty
		if (Dir::isEmpty($dir) === true) {
			Dir::remove($dir);
		}
	}

	/**
	 * Throws an exception with the appropriate translated error message
	 *
	 * @throws \Exception Any upload error
	 */
	public static function error(int $error): void
	{
		// get error messages from translation
		$message = [
			UPLOAD_ERR_INI_SIZE   => I18n::translate('upload.error.iniSize'),
			UPLOAD_ERR_FORM_SIZE  => I18n::translate('upload.error.formSize'),
			UPLOAD_ERR_PARTIAL    => I18n::translate('upload.error.partial'),
			UPLOAD_ERR_NO_FILE    => I18n::translate('upload.error.noFile'),
			UPLOAD_ERR_NO_TMP_DIR => I18n::translate('upload.error.tmpDir'),
			UPLOAD_ERR_CANT_WRITE => I18n::translate('upload.error.cantWrite'),
			UPLOAD_ERR_EXTENSION  => I18n::translate('upload.error.extension')
		];

		throw new Exception(
			message: $message[$error] ?? I18n::translate('upload.error.default', 'The file could not be uploaded')
		);
	}

	/**
	 * Sanitize the filename and extension
	 * based on the detected mime type
	 */
	public static function filename(array $upload): string
	{
		// get the extension of the uploaded file
		$extension = F::extension($upload['name']);

		// try to detect the correct mime and add the extension
		// accordingly. This will avoid .tmp filenames
		if (
			empty($extension) === true ||
			in_array($extension, ['tmp', 'temp'], true) === true
		) {
			$mime      = F::mime($upload['tmp_name']);
			$extension = F::mimeToExtension($mime);
			$filename  = F::name($upload['name']) . '.' . $extension;
			return $filename;
		}

		return basename($upload['name']);
	}

	/**
	 * Upload the files and call closure for each file
	 *
	 * @throws \Exception Any upload error
	 */
	public function process(Closure $callback): array
	{
		$files   = $this->api->requestFiles();
		$uploads = [];
		$errors  = [];

		static::validateFiles($files);

		foreach ($files as $upload) {
			if (
				isset($upload['tmp_name']) === false &&
				is_array($upload) === true
			) {
				continue;
			}

			try {
				if ($upload['error'] !== 0) {
					static::error($upload['error']);
				}

				$filename = static::filename($upload);
				$source   = $this->source($upload['tmp_name'], $filename);

				// if the file is uploaded in chunks…
				if ($this->api->requestHeaders('Upload-Length')) {
					$source = $this->processChunk($source, $filename);
				}

				// apply callback only to complete uploads
				// (incomplete chunk request will return empty $source)
				$data = match ($source) {
					null    => null,
					default => $callback($source, $filename)
				};

				$uploads[$upload['name']] = match (true) {
					is_object($data) => $this->api->resolve($data)->toArray(),
					default          => $data
				};
			} catch (Exception $e) {
				$errors[$upload['name']] = $e->getMessage();

				// clean up file from system tmp directory
				F::unlink($upload['tmp_name']);
			}

			if ($this->single === true) {
				break;
			}
		}

		return static::response($uploads, $errors);
	}

	/**
	 * Handle chunked uploads by merging all chunks
	 * in the tmp directory and only returning the new
	 * $source path to the tmp file once complete
	 *
	 * @throws \Kirby\Exception\DuplicateException Duplicate first chunk (same filename and id)
	 * @throws \Kirby\Exception\Exception Chunk offset does not match existing tmp file
	 * @throws \Kirby\Exception\InvalidArgumentException Too short ID string
	 * @throws \Kirby\Exception\NotFoundException Subsequent chunk has no  existing tmp file
	 */
	public function processChunk(
		string $source,
		string $filename
	): string|null {
		// ensure the tmp upload directory exists
		Dir::make($dir = static::tmpDir());

		// create path for file in tmp upload directory;
		// prefix with id while file isn't completely uploaded yet
		$id       = $this->api->requestHeaders('Upload-Id', '');
		$id       = static::chunkId($id);
		$total    = (int)$this->api->requestHeaders('Upload-Length');
		$filename = basename($filename);
		$tmpRoot  = $dir . '/' . $id . '-' . $filename;

		// validate various aspects of the request
		// to ensure the chunk isn't trying to do malicious actions
		static::validateChunk(
			source:   $source,
			tmp:      $tmpRoot,
			total:    $total,
			offset:   $this->api->requestHeaders('Upload-Offset'),
			template: $this->api->requestBody('template'),
		);

		// stream chunk content and append it to partial file
		stream_copy_to_stream(
			fopen($source, 'r'),
			fopen($tmpRoot, 'a')
		);

		// clear file stat cache so the following call to `F::size`
		// really returns the updated file size
		clearstatcache();

		// if file isn't complete yet, return early
		if (F::size($tmpRoot) < $total) {
			return null;
		}

		// remove id from partial filename now the file is complete,
		// so we can pass the path from the tmp upload directory
		// as new source path for the file back to the API upload method
		rename(
			$tmpRoot,
			$source = $dir . '/' . $filename
		);

		return $source;
	}

	/**
	 * Convert uploads and errors in response array for API response
	 */
	public static function response(
		array $uploads,
		array $errors
	): array {
		if (count($uploads) + count($errors) <= 1) {
			if (count($errors) > 0) {
				return [
					'status'  => 'error',
					'message' => current($errors)
				];
			}

			return [
				'status' => 'ok',
				'data'   => $uploads ? current($uploads) : null
			];
		}

		if (count($errors) > 0) {
			return [
				'status' => 'error',
				'errors' => $errors
			];
		}

		return [
			'status' => 'ok',
			'data'   => $uploads
		];
	}

	/**
	 * Move the tmp file to a location including the extension,
	 * for better mime detection and return updated source path
	 *
	 * @codeCoverageIgnore
	 */
	public function source(string $source, string $filename): string
	{
		if ($this->debug === true) {
			return $source;
		}

		$target = dirname($source) . '/' . uniqid() . '.' . $filename;

		if (move_uploaded_file($source, $target)) {
			return $target;
		}

		throw new Exception(
			message: I18n::translate('upload.error.cantMove')
		);
	}

	/**
	 * Returns root of directory used for
	 * temporarily storing (incomplete) uploads
	 * @codeCoverageIgnore
	 */
	protected static function tmpDir(): string
	{
		return App::instance()->root('cache') . '/.uploads';
	}

	/**
	 * Ensures the sent chunk is valid
	 *
	 * @throws \Kirby\Exception\DuplicateException Duplicate first chunk (same filename and id)
	 * @throws \Kirby\Exception\InvalidArgumentException Chunk offset does not match existing tmp file
	 * @throws \Kirby\Exception\InvalidArgumentException The maximum file size for this blueprint was exceeded
	 * @throws \Kirby\Exception\NotFoundException Subsequent chunk has no  existing tmp file
	 */
	protected static function validateChunk(
		string $source,
		string $tmp,
		int $total,
		int $offset,
		string|null $template = null
	): void {
		$file = new File([
			'parent'   => new Page(['slug' => 'tmp']),
			'filename' => $filename = basename($tmp),
			'template' => $template
		]);

		// if the blueprint `maxsize` option is set,
		// ensure that the total size communicated in the header
		// as well as the current tmp size after adding this chunk
		// do not exceed the max limit
		if (
			($max = $file->blueprint()->accept()['maxsize'] ?? null) &&
			(
				$total > $max ||
				(F::size($source) + F::size($tmp)) > $max
			)
		) {
			throw new InvalidArgumentException(
				key: 'file.maxsize'
			);
		}

		// validate the first chunk
		if ($offset === 0) {
			// sent chunk is expected to be the first part,
			// but tmp file already exists
			if (F::exists($tmp) === true) {
				throw new DuplicateException(
					message: 'A tmp file upload with the same filename and upload id already exists: ' . $filename
				);
			}

			// validate file (extension, name) for first chunk;
			// will also be validate again by `$model->createFile()`
			// when completely uploaded
			FileRules::validFile($file, false);

			// first chunk is valid
			return;
		}

		// validate subsequent chunks:
		// no tmp in place
		if (F::exists($tmp) === false) {
			throw new NotFoundException(
				message: 'Chunk offset ' . $offset . ' for non-existing tmp file: ' . $filename
			);
		}

		// sent chunk's offset is not the continuation of the tmp file
		if ($offset !== F::size($tmp)) {
			throw new InvalidArgumentException(
				message: 'Chunk offset ' . $offset . ' does not match the existing tmp upload file size of ' . F::size($tmp)
			);
		}
	}

	/**
	 * Validate the files array for upload
	 *
	 * @throws \Exception No files were uploaded
	 */
	protected static function validateFiles(array $files): void
	{
		if ($files === []) {
			$postMaxSize       = Str::toBytes(ini_get('post_max_size'));
			$uploadMaxFileSize = Str::toBytes(ini_get('upload_max_filesize'));

			// @codeCoverageIgnoreStart
			if ($postMaxSize < $uploadMaxFileSize) {
				throw new Exception(
					message:
					I18n::translate(
						'upload.error.iniPostSize',
						'The uploaded file exceeds the post_max_size directive in php.ini'
					)
				);
			}
			// @codeCoverageIgnoreEnd

			throw new Exception(
				message:
				I18n::translate(
					'upload.error.noFiles',
					'No files were uploaded'
				)
			);
		}
	}
}
