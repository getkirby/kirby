<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * This is a helper class to turn paths into the correct
 * response objects (Page, File, Responder)
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PathFinder
{
	protected Site $site;

	public function __construct(
		protected App $kirby,
	) {
		// the site is needed a couple times here
		$this->site = $kirby->site();
	}

	public function find(
		string|null $path,
		VersionId $versionId,
		string|null $token = null,
	): Page|File|Responder {
		// use the home page if the path is empty
		if ($path === null || $path === '' || $path === '/') {
			return $this->findHomePage(
				versionId: $versionId,
				token: $token
			);
		}

		// directly prevent path with incomplete content representation
		if (Str::endsWith($path, '.') === true) {
			throw new InvalidArgumentException('Incomplete file or content representation extension');
		}

		// try to get a page by path
		if ($page = $this->site->findPageOrDraft($path)) {
			return $this->findPage(
				page: $page,
				versionId: $versionId,
				extension: F::extension($path),
				token: $token
			);
		}

		// try to resolve a path that leads to a file
		return $this->findFile(
			path: $path,
			versionId: $versionId,
			token: $token
		);
	}

	public function findErrorPage(
		VersionId $versionId,
		string|null $token = null
	): Page {
		if ($errorPage = $this->site->errorPage()) {
			return $this->findPage(
				page: $errorPage,
				versionId: $versionId,
				token: $token
			);
		}

		throw new NotFoundException('The error page does not exist');
	}

	/**
	 * @throws \Kirby\Exception\PermissionException if the site, page or version cannot be viewed
	 * @throws \Kirby\Exception\InvalidArgumentException if the path does not contain a filename
	 */
	protected function findFile(
		string $path,
		VersionId $versionId,
		string|null $token = null
	): File|Page {
		$id        = dirname($path);
		$filename  = basename($path);
		$extension = F::extension($filename);

		// without an extension, it's not a file path
		if (empty($extension) === true) {
			throw new InvalidArgumentException('The path does not contain a valid filename');
		}

		// try to resolve file urls for pages and drafts
		if ($page = $this->site->findPageOrDraft($id)) {
			return $this->findPageFile(
				page: $page,
				versionId: $versionId,
				filename: $filename,
				token: $token
			);
		}

		// try to resolve site files at least
		return $this->findSiteFile(
			site: $this->site,
			versionId: $versionId,
			filename: $filename,
			token: $token
		);
	}

	/**
	 * @throws \Kirby\Exception\PermissionException if the page or version cannot be viewed
	 * @throws \Kirby\Exception\NotFoundException if the home page cannot be found
	 */
	protected function findHomePage(
		VersionId $versionId,
		string|null $token = null
	): Page {
		if ($homePage = $this->site->homePage()) {
			return $this->findPage(
				page: $homePage,
				versionId: $versionId,
				token: $token
			);
		}

		throw new NotFoundException('The home page does not exist');
	}

	/**
	 * @throws \Kirby\Exception\PermissionException if the page or version cannot be viewed
	 * @throws \Kirby\Exception\NotFoundException if the page representation cannot be found
	 */
	protected function findPage(
		Page $page,
		VersionId $versionId,
		string|null $extension = null,
		string|null $token = null
	): Page|Responder {
		// handle content representations if there's an extension
		if (empty($extension) === false) {
			return $this->findPageRepresentation(
				page: $page,
				versionId: $versionId,
				extension: $extension,
				token: $token
			);
		}

		$this->validatePageAccess(
			page: $page,
			versionId: $versionId,
			token: $token
		);

		return $page;
	}

	/**
	 * @throws \Kirby\Exception\PermissionException if the page or version cannot be viewed
	 * @throws \Kirby\Exception\NotFoundException if the file cannot be found
	 */
	protected function findPageFile(
		Page $page,
		VersionId $versionId,
		string $filename,
		string|null $token = null
	): File {
		$this->validatePageAccess(
			page: $page,
			versionId: $versionId,
			token: $token
		);

		if ($file = $page->file($filename)) {
			return $file;
		}

		throw new NotFoundException('The file could not be found');
	}

	/**
	 * @throws \Kirby\Exception\PermissionException if the page or version cannot be viewed
	 * @throws \Kirby\Exception\NotFoundException if the page representation cannot be found
	 */
	protected function findPageRepresentation(
		Page $page,
		VersionId $versionId,
		string $extension,
		string|null $token = null
	): Responder|Page {
		$this->validatePageAccess(
			page: $page,
			versionId: $versionId,
			token: $token
		);

		$response = $this->kirby->response();
		$output   = $page->render([], $extension);

		// attach a MIME type based on the representation
		// only if no custom MIME type was set
		if ($response->type() === null) {
			$response->type($extension);
		}

		return $response->body($output);
	}

	protected function findSiteFile(
		Site $site,
		VersionId $versionId,
		string $filename,
		string|null $token = null
	): File {
		// @todo: verify the version here as well
		// the site class does not have any methods
		// for this so far.

		if ($file = $site->file($filename)) {
			return $file;
		}

		throw new NotFoundException('The file could not be found');
	}

	/**
	 * Checks if the page or the requested version is viewable
	 */
	protected function validatePageAccess(
		Page $page,
		VersionId $versionId,
		string|null $token = null
	): void {
		// only show the file of a page, if the page can be viewed
		if ($page->isViewable($versionId, $token) === false) {
			throw new PermissionException('The page version cannot be viewed');
		}
	}
}
