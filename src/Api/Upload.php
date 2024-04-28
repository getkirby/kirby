<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\FileRules;
use Kirby\Cms\Page;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;

/**
 * The Upload class supports file uploads in the
 * context of the API
 *
 * @package   Kirby Api
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.3.0
 * @internal
 */
class Upload
{
	/**
	 * Handle chunked uploads by merging all chunks
	 * in the tmp directory and only returning the new
	 * $source path to the tmp file once complete
	 *
	 * @throws \Kirby\Exception\DuplicateException Duplicate first chunk (same filename and id)
	 * @throws \Kirby\Exception\Exception Chunk offset does not match existing tmp file
	 * @throws \Kirby\Exception\NotFoundException Subsequent chunk has no  existing tmp file
	 */
	public static function chunk(
		Api $api,
		string $source,
		string $filename
	): string|null {
		// if the file is uploaded in chunks…
		if ($total = (int)$api->requestHeaders('Upload-Length')) {
			// ensure the tmp upload directory exists
			Dir::make($dir = static::tmp());

			// create path for file in tmp upload directory;
			// prefix with id while file isn't completely uploaded yet
			$id       = static::chunkId($api->requestHeaders('Upload-Id'));
			$filename = basename($filename);
			$tmpRoot  = $dir . '/' . $id . '-' . $filename;

			// validate various aspects of the request
			// to ensure the chunk isn't trying to do malicious actions
			static::validateChunk(
				source:   $source,
				tmp:      $tmpRoot,
				total:    $total,
				offset:   $api->requestHeaders('Upload-Offset'),
				template: $api->requestBody('template'),
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
				$newRoot = $dir . '/' . $filename
			);

			return $newRoot;
		}

		return $source;
	}

	/**
	 * Ensures a clean chunk ID by stripping forbidden characters
	 */
	public static function chunkId(string $id): string
	{
		return Str::slug($id, '', 'a-z0-9');
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
	public static function clean(): void
	{
		foreach (Dir::files($dir = static::tmp(), [], true) as $file) {
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
	 * Returns root of directory used for
	 * temporarily storing (incomplete) uploads
	 * @codeCoverageIgnore
	 */
	protected static function tmp(): string
	{
		return App::instance()->root('cache') . '/.uploads';
	}

	/**
	 * Ensures the sent chunk is valid
	 *
	 * @throws \Kirby\Exception\DuplicateException Duplicate first chunk (same filename and id)
	 * @throws \Kirby\Exception\Exception Chunk offset does not match existing tmp file
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
		// does not exceed the max limit
		if (
			($max = $file->blueprint()->accept()['maxsize'] ?? null) &&
			(
				$total > $max ||
				(F::size($source) + F::size($tmp)) > $max
			)
		) {
			throw new InvalidArgumentException(['key' => 'file.maxsize']);
		}

		// validate the first chunk
		if ($offset === 0) {
			// sent chunk is expected to be the first part,
			// but tmp file already exists
			if (F::exists($tmp) === true) {
				throw new DuplicateException('A tmp file upload with the same filename and upload id already exists: ' . $filename);
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
			throw new NotFoundException('Chunk offset ' . $offset . ' for non-existing tmp file: ' . $filename);
		}

		// sent chunk's offset is not the continuation of the tmp file
		if ($offset !== F::size($tmp)) {
			throw new Exception('Chunk offset ' . $offset . ' does not match the existing tmp upload file size of ' . F::size($tmp));
		}
	}
}
