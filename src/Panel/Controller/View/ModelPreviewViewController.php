<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\PermissionException;
use Kirby\Http\Uri;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Panel;
use Kirby\Panel\Redirect;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\A;

/**
 * Controls the preview view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelPreviewViewController extends ViewController
{
	public function __construct(
		public Page|Site $model,
		public string $mode
	) {
		parent::__construct();
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view(view: $this->id(), model: $this->model)->defaults(
			'languages',
			$this->model::CLASS_ALIAS . '.versions',
		)
			->bind(['mode' => $this->mode]);
	}

	public static function factory(string $path, string $mode): static
	{
		return new static(
			model: Find::parent($path),
			mode:  $mode
		);
	}

	public function id(): string
	{
		return $this->model::CLASS_ALIAS . '.preview';
	}

	public function load(): View
	{
		// handle redirect if view was reloaded with a redirect URL
		// after navigating to a different page inside the preview browser
		if ($redirect = $this->redirect()) {
			Panel::go($redirect);
		}

		$props = $this->props();
		return new View(...$props);
	}

	/**
	 * Resolves the provided URL to an existing site/page.
	 * @throws \Kirby\Panel\Redirect When no model can be found for the URL
	 */
	protected function modelFromUri(Uri $url): Site|Page
	{
		$model = $this->kirby->call($url->path(), 'GET');

		// @codeCoverageIgnoreStart
		if (
			$model instanceof Site === false &&
			$model instanceof Page === false
		) {
			throw new Redirect(location: $url->toString());
		}
		// @codeCoverageIgnoreEnd

		return $model;
	}

	public function props(): array
	{
		return [
			'component' => 'k-preview-view',
			'buttons'   => $this->buttons(),
			'src'       => $this->src(),
			'mode'      => $this->mode,
			'viewports' => $this->kirby->option('panel.preview.viewports')
		];
	}

	public function redirect(): string|null
	{
		// Get redirect URL path
		if ($redirect = $this->request->get('view')) {
			$redirect = new Uri($redirect);

			// Look up new model and redirect to its preview
			if ($model = $this->modelFromUri($redirect)) {
				$url = $model->panel()->url() . '/preview/' . $this->mode;
				$url = new Uri($url);

				// Preserve the redirect URL's query and params
				// and inject them into the new URL
				unset(
					$redirect->query()->_token,
					$redirect->query()->_version,
					$redirect->query()->_preview
				);

				if ($redirect->query()->isNotEmpty() === true) {
					$url->query()->_query = $redirect->query()->toString();
				}

				if ($redirect->params()->isNotEmpty() === true) {
					$url->query()->_params = $redirect->params()->toString();
				}

				return $url->toString();
			}
		}

		return null;
	}

	public function src(): array
	{
		$model = $this->model;

		// if view is reloaded with a different browser URL,
		// find corresponding model for this URL
		// and use it for the preview URLs
		if ($browser = $this->request->get('browser')) {
			$browser = new Uri($browser);
			$model   = $this->modelFromUri($browser);
		}

		// add the relevant preview URLs
		// depending on the preview view mode
		$src = [];

		if (
			$this->mode === 'latest' ||
			$this->mode === 'compare'
		) {
			$src['latest'] = $model->previewUrl('latest');
		}

		if (
			$this->mode === 'changes' ||
			$this->mode === 'compare' ||
			$this->mode === 'form'
		) {
			$src['changes'] = $model->previewUrl('changes');
		}

		if (array_filter($src) === []) {
			throw new PermissionException('The preview is not available');
		}

		// merge required params & query into preview URLs
		return A::map(
			$src,
			function (string $url) use ($browser): string {
				$uri = new Uri($url);

				// if a specific browser URL was provided,
				// copy its params and query
				if ($browser !== null) {
					$uri->inherit($browser);
				}

				// set the preview flag
				$uri->query()->_preview = 'true';

				// inject params and query from a redirect
				if ($params = $this->request->get('_params')) {
					$uri->params()->merge($params);
				}
				if ($query = $this->request->get('_query')) {
					$uri->query()->merge($query);
				}

				return $uri->toString();
			}
		);
	}
}
