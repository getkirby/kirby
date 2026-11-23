<?php

namespace Kirby\Cms\App;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Responder;
use Kirby\Cms\Response;
use Kirby\Cms\Site;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Mime;

/**
 * Request Path Resolver
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Resolver
{
	protected Site $site;

	public function __construct(
		protected App $kirby,
	) {
		$this->site = $kirby->site();
	}

	/**
	 * Returns the content representation/file extension from a path
	 */
	protected function detectExtension(string $path): string|null
	{
		$extension = F::extension($path);

		if ($extension === '') {
			return null;
		}

		return $extension;
	}

	/**
	 * Checks whether the page has at least one content
	 * representation that can be selected through content
	 * negotiation, i.e. a representation template with a
	 * known non-HTML MIME type
	 */
	protected function hasNegotiableRepresentation(Page $page): bool
	{
		$template  = $page->template();
		$name      = $template->name();
		$extension = $template->extension();
		$store     = $template->store();

		// representation templates in the templates root
		// and those registered by plugins (`name.type`)
		$files = glob($template->root() . '/' . $name . '.*.' . $extension);
		$names = [
			...array_map(
				static fn (string $file) => basename($file, '.' . $extension),
				$files === false ? [] : $files
			),
			...array_keys($this->kirby->extensions($store))
		];

		foreach ($names as $candidate) {
			if (str_starts_with($candidate, $name . '.') === false) {
				continue;
			}

			$type = substr($candidate, strlen($name) + 1);
			$mime = Mime::fromExtension($type);

			if ($mime !== null && $mime !== 'text/html') {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether a draft can be accessed by the current user
	 * or through a token-authenticated request.
	 */
	protected function isAccessibleDraft(Page $page): bool
	{
		return
			($this->kirby->user() !== null && $page->isAccessible() === true) ||
			$page->renderVersionFromRequest() !== null;
	}

	/**
	 * Checks whether a file may be served through a clean URL,
	 * based on the `content.fileRedirects` option
	 */
	public function isResolvableFile(File $file): bool
	{
		$option = $this->kirby->option('content.fileRedirects', false);

		if ($option === true) {
			return true;
		}

		if ($option instanceof Closure) {
			return $option($file) === true;
		}

		// option was set to `false` or an invalid value
		return false;
	}

	/**
	 * Used in routes to resolve request paths
	 *
	 * @throws NotFoundException if the page does not exist
	 */
	public function resolve(string|null $path = null): File|Page|Responder|Response
	{
		// directly prevent path with incomplete content representation
		if (str_ends_with($path ?? '', '.') === true) {
			throw new NotFoundException(
				message: 'Incomplete content representation'
			);
		}

		// home page
		if ($path === null) {
			return $this->resolveHomePage();
		}

		// get a potential content representation or file extension
		$extension = $this->detectExtension($path);

		// the path leads directly to a public page
		if ($page = $this->site->children()->find($path)) {
			return $this->resolvePage($page, $extension);
		}

		// the path leads to a draft
		if ($draft = $this->site->draft($path)) {
			return $this->resolveDraft($draft, $extension);
		}

		// no file
		if ($extension === null) {
			throw new NotFoundException(
				key: 'page.undefined'
			);
		}

		// resolve filenames without path to site files
		if (str_contains($path, '/') === false) {
			return $this->resolveSiteFile($path);
		}

		return $this->resolvePageFile($path);
	}

	/**
	 * @throws NotFoundException if the draft cannot be accessed
	 */
	protected function resolveDraft(Page $draft, string|null $extension = null): Page|Responder|Response
	{
		if ($this->isAccessibleDraft($draft) === true) {
			return $this->resolvePage($draft, $extension);
		}

		throw new NotFoundException(
			key: 'page.undefined'
		);
	}

	/**
	 * @throws NotFoundException if the file cannot be found or accessed
	 */
	protected function resolveFile(File|null $file): File
	{
		if ($file === null || $this->isResolvableFile($file) === false) {
			throw new NotFoundException(
				key: 'file.undefined'
			);
		}

		return $file;
	}

	/**
	 * @throws LogicException if the home page does not exist
	 */
	protected function resolveHomePage(): Page|Responder|Response
	{
		if ($page = $this->site->homePage()) {
			return $this->resolvePage($page);
		}

		// a missing home page is a hard configuration error, not a 404;
		// throwing a LogicException lets it bubble up past App::call()
		// instead of being turned into the error page
		throw new LogicException(
			message: 'The home page does not exist'
		);
	}

	/**
	 * @throws NotFoundException if the page cannot be found
	 */
	protected function resolvePage(Page $page, string|null $extension = null): Page|Responder|Response
	{
		if ($extension === null) {
			// no explicit representation in the URL: use content negotiation
			// to pick a representation based on the accepted MIME types,
			// otherwise return the page for regular HTML rendering
			return $this->resolvePreferredRepresentation($page) ?? $page;
		}

		// a content representation was requested through the URL;
		// if extension is the default content type,
		// redirect to the page URL without extension
		if ($extension === 'html') {
			return Response::redirect($page->url(), 301, inherit: true);
		}

		return $this->resolvePageResponse($page, $extension);
	}

	/**
	 * @throws NotFoundException if the file cannot be found or accessed
	 */
	protected function resolvePageFile(string $path): File
	{
		$id        = dirname($path);
		$filename  = basename($path);

		if ($page = $this->site->findPageOrDraft($id)) {
			// make sure to not leak files on draft pages through clean URLs:
			// only serve them to an authenticated user with access
			// permission or a request with a valid preview token
			if ($page->isDraft() === false || $this->isAccessibleDraft($page) === true) {
				return $this->resolveFile($page->file($filename));
			}
		}

		throw new NotFoundException(
			key: 'file.undefined'
		);
	}

	protected function resolvePageResponse(Page $page, string $extension): Responder
	{
		$response = $this->kirby->response();
		$output   = $page->render([], $extension);

		// attach a MIME type based on the representation
		// only if no custom MIME type was set
		if ($response->type() === null) {
			$response->type($extension);
		}

		$response->body($output);

		return $response;
	}

	/**
	 * Resolves a page to a content representation based on the
	 * MIME types accepted by the visitor (content negotiation).
	 * Returns a prepared response for the preferred representation
	 * or `null` if the page should be rendered as regular HTML.
	 */
	protected function resolvePreferredRepresentation(Page $page): Responder|null
	{
		// if the page has any representation that can be negotiated,
		// the response at the clean URL depends on the Accept header,
		// no matter which representation is picked for this request
		// (including HTML); let caches/proxies know so they don't mix
		// up the different variants
		if ($this->hasNegotiableRepresentation($page) === true) {
			$this->varyByAccept();
		}

		// walk the accepted MIME types in quality order (highest first)
		foreach ($this->kirby->visitor()->acceptedMimeTypes() as $accepted) {
			$mime = $accepted->type();

			// HTML (the default output) already satisfies this Accept
			// entry, including wildcards like `text/*` or `*/*`
			// (browsers, curl) -> stop and render the page as usual
			if (Mime::matches('text/html', $mime) === true) {
				return null;
			}

			// probe each extension the MIME type maps to
			// for an existing content representation
			foreach (Mime::toExtensions($mime) as $extension) {
				try {
					$page->representation($extension);
				} catch (NotFoundException) {
					// no representation for this type, try the next
					continue;
				}

				// make sure the negotiated response always varies
				// by Accept, even if the representation could not
				// be detected upfront (e.g. custom template component)
				$this->varyByAccept();

				return $this->resolvePageResponse($page, $extension);
			}
		}

		// nothing matched -> render the page as HTML
		return null;
	}

	/**
	 * @throws NotFoundException if the file cannot be found or accessed
	 */
	protected function resolveSiteFile(string $path): File
	{
		return $this->resolveFile($this->site->file($path));
	}

	/**
	 * Lets the response vary by the Accept header;
	 * the value is marked as volatile as it only applies
	 * to the clean page URL, not to the same representation
	 * requested with its explicit extension (shared cache entry)
	 */
	protected function varyByAccept(): void
	{
		$response = $this->kirby->response();
		$response->vary('Accept');
		$response->volatileHeaders()->mark('Vary', ['Accept']);
	}
}
