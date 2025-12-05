<?php

namespace Kirby\Panel\Response;

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Panel\Assets;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The View response class handles State
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
			'assets'     => $this->assets->external(),
			'icons'      => $this->assets->icons(),
			'nonce'      => $this->kirby->nonce(),
			'state'      => $this->data(),
			'panelUrl'   => $this->url(),
		]);
	}

	/**
	 * Returns the full state data object
	 */
	public function data(bool $globals = true): array
	{
		return parent::data($globals);
	}

	/**
	 * Renders the main panel view either as
	 * full HTML document based on the request
	 * header or query params
	 */
	public static function from(mixed $data): Response
	{
		// Create an error view for any route that throws a not found exception.
		// This will make sure that users can navigate to such views and will get a
		// useful response instead of the debugger or the fatal screen.
		if ($data instanceof NotFoundException) {
			return static::error($data->getMessage());
		}

		return parent::from($data);
	}

	public function headers(): array
	{
		return [
			'Content-Security-Policy' => $this->cors(),
		];
	}

	/**
	 * Full HTML response
	 * @codeCoverageIgnore
	 */
	public function send(): string
	{
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
