<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;

/**
 * This is a helper class to turn paths into the correct objects
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PathResolver
{
	protected Site $site;

	public function __construct(
		protected App $kirby,
	) {
		// the site is needed a couple times here
		$this->site = $kirby->site();
	}

	public function resolve(
		string|null $path = null,
		string|null $versionId = null,
		string|null $token = null,
	): Page|File|Responder {
		// store which version should be resolved
		$versionId = VersionId::from($versionId);

		// use the home page if the path is empty
		if ($path === null) {
			return $this->resolveHomePage($versionId, $token);
		}

		// directly prevent path with incomplete content representation
		if (Str::endsWith($path, '.') === true) {
			return $this->resolveErrorPage($versionId, $token);
		}

		// try to get a page by path
		if ($page = $this->site->findPageOrDraft($path)) {
			return $this->resolvePage($page, $versionId, F::extension($path), $token);
		}

		return $this->resolveFile($path, $versionId, $token);
	}

	protected function resolveErrorPage(
		VersionId $versionId,
		string|null $token = null
	): Page {
		if ($errorPage = $this->site->errorPage()) {
			return $this->resolvePage($errorPage, $versionId);
		}

		throw new NotFoundException('The error page does not exist');
	}

	protected function resolveFile(
		string $path, VersionId $versionId,
		string|null $token = null
	): File|Page {
		$id       = dirname($path);
		$filename = basename($path);

		// try to resolve file urls for pages and drafts
		if ($page = $this->site->findPageOrDraft($id)) {
			return $this->resolvePageFile($page, $versionId, $filename, $token);
		}

		// try to resolve site files at least
		return $this->resolveSiteFile($this->site, $versionId, $filename, $token);
	}

	protected function resolveHomePage(
		VersionId $versionId,
		string|null $token = null
	): Page {
		if ($homePage = $this->site->homePage()) {
			return $this->resolvePage($homePage, $versionId);
		}

		throw new NotFoundException('The home page does not exist');
	}

	protected function resolvePage(
		Page $page,
		VersionId $versionId,
		string|null $extension = null,
		string|null $token = null
	): Page|Responder {
		if ($page->isViewable($versionId, $token) === false) {
			throw new PermissionException('The page version cannot be viewed');
		}

		// activate the changes preview if requested
		if ($versionId->is(VersionId::changes()) === true) {
			VersionId::$render = $versionId;
		}

		if (empty($extension) === false) {
			return $this->resolvePageRepresentation($page, $versionId, $extension);
		}

		return $page;
	}

	protected function resolvePageFile(
		Page $page,
		VersionId $versionId,
		string $filename,
		string|null $token = null
	) {
		// only show the file of a page, if the page can be viewed
		if ($page->isViewable($versionId, $token) === false) {
			throw new PermissionException('The page version cannot be viewed');
		}

		return $page->file($filename) ?? $this->resolveErrorPage($versionId, $token);
	}

	protected function resolvePageRepresentation(
		Page $page,
		VersionId $versionId,
		string $extension,
		string|null $token = null
	): Responder {
		$response = $this->kirby->response();
		$output   = $page->render([], $extension);

		// attach a MIME type based on the representation
		// only if no custom MIME type was set
		if ($response->type() === null) {
			$response->type($extension);
		}

		return $response->body($output);
	}

	protected function resolveSiteFile(
		Site $site,
		VersionId $versionId,
		string $filename,
		string|null $token = null
	) {
		return $site->file($filename) ?? $this->resolveErrorPage($versionId, $token);
	}

}
