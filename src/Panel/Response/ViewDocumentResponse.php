<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Panel\Assets;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The View response class handles Fiber
 * requests to render either a JSON object
 * or a full HTML document for Panel views
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ViewDocumentResponse extends ViewResponse
{
	protected Assets $assets;
	protected App $kirby;

	public function __construct(
		protected array $view = [],
		protected int $code = 200
	) {
		parent::__construct(
			view: $view,
			code: $code,
		);

		$this->assets = new Assets();
		$this->kirby  = App::instance();
	}

	/**
	 * Returns Content-Security-Policy header
	 */
	public function cors(): string
	{
		$ancestors = $this->kirby->option('panel.frameAncestors');

		return 'frame-ancestors ' . match (true) {
			$ancestors === true   => "'self'",
			is_array($ancestors)  => "'self' " . implode(' ', $ancestors),
			is_string($ancestors) => $ancestors,
			default               => "'none'"
		};
	}

	public function body(): string
	{
		$template = $this->kirby->root('kirby') . '/views/panel.php';

		return Tpl::load($template, [
			'assets'   => $this->assets->external(),
			'icons'    => $this->assets->icons(),
			'nonce'    => $this->kirby->nonce(),
			'fiber'    => $this->data(),
			'panelUrl' => $this->url(),
		]);
	}

	/**
	 * Returns the full fiber data object
	 */
	public function data(): array
	{
		return $this->fiber()->toArray(globals: true);
	}

	public function headers(): array
	{
		return [
			'Content-Security-Policy' => $this->cors(),
		];
	}

	public function send(): string
	{
		// Full HTML response
		// @codeCoverageIgnoreStart
		try {
			if ($this->assets->link() === true) {
				usleep(1);
				return Response::redirect(
					$this->kirby->url('base') . '/' . $this->kirby->path()
				);
			}
		} catch (Throwable $e) {
			return new Response(
				body: 'The Panel assets cannot be installed properly. ' . $e->getMessage(),
				code: 500
			);
		}
		// @codeCoverageIgnoreEnd

		return parent::send();
	}

	public function type(): string
	{
		return 'text/html';
	}

	public function url(): string
	{
		// get the uri object for the Panel url
		$uri = new Uri($this->kirby->url('panel'));

		return $uri->path()->toString(true) . '/';
	}
}
