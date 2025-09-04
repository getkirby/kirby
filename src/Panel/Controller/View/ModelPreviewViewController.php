<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Uri;
use Kirby\Toolkit\A;

/**
 * Temporary class to support the preview view
 * and bundle logic before the actual class will be
 * added in Kirby 6.0.0
 *
 * @internal
 */
class ModelPreviewViewController
{
	public static function redirect(string $versionId): string|null
	{
		$kirby = App::instance();

		// Get redirect URL path
		if ($redirect = $kirby->request()->get('redirect')) {
			$redirect = new Uri($redirect);

			// Look up new model and redirect to its preview
			if ($result = $kirby->call($redirect->path, 'GET')) {

				// @codeCoverageIgnoreStart
				if ($result instanceof ModelWithContent === false) {
					throw new LogicException(
						message: 'Cannot redirect the preview view to an URL that does not belong to any model'
					);
				}
				// @codeCoverageIgnoreEnd

				$url = $result->panel()->url() . '/preview/' . $versionId;
				$url = new Uri($url);

				// Preserve the redirect URL's query and params
				// and inject them into the new URL
				unset(
					$redirect->query->_token,
					$redirect->query->_version,
					$redirect->query->_preview
				);

				if ($redirect->query->isNotEmpty() === true) {
					$url->query->_query = $redirect->query->toString();
				}

				if ($redirect->params->isNotEmpty() === true) {
					$url->query->_params = $redirect->params->toString();
				}

				return $url->toString();
			}
		}

		return null;
	}

	public static function src(Page|Site $model): array
	{
		$src = [
			'latest'  => $model->previewUrl('latest'),
			'changes' => $model->previewUrl('changes'),
		];

		if ($src['latest'] === null) {
			throw new PermissionException('The preview is not available');
		}

		return A::map(
			$src,
			function (string $url) use ($model): string {
				$uri = new Uri($url);

				// set the preview flag
				$uri->query->_preview = 'true';

				// inject params and query from a redirect
				$uri->params->merge($model->kirby()->request()->get('_params'));
				$uri->query->merge($model->kirby()->request()->get('_query'));

				return $uri->toString();
			}
		);
	}
}
