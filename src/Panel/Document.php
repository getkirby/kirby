<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Toolkit\Tpl;
use Throwable;

/**
 * The Document is used by the View class to render
 * the full Panel HTML document in Fiber calls that
 * should not return just JSON objects
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Document
{
	protected Assets $assets;
	protected App $kirby;

	public function __construct()
	{
		$this->kirby = App::instance();
	}

	public function assets(): Assets
	{
		return $this->assets ??= new Assets();
	}

	/**
	 * Load the main Panel view template
	 */
	public function body(array $fiber): string
	{
		$template = $this->kirby->root('kirby') . '/views/panel.php';

		return Tpl::load($template, [
			'assets'   => $this->assets()->external(),
			'icons'    => $this->assets()->icons(),
			'nonce'    => $this->kirby->nonce(),
			'fiber'    => $fiber,
			'panelUrl' => $this->url(),
		]);
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

	/**
	 * Renders the panel document
	 */
	public function render(array $fiber): Response
	{
		// Full HTML response
		// @codeCoverageIgnoreStart
		try {
			if ($this->assets()->link() === true) {
				usleep(1);
				Response::go(
					$this->kirby->url('base') . '/' . $this->kirby->path()
				);
			}
		} catch (Throwable $e) {
			die('The Panel assets cannot be installed properly. ' . $e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		return new Response(
			body: $this->body($fiber),
			type: 'text/html',
			code: $fiber['view']['code'] ?? 200,
			headers: [
				'Content-Security-Policy' => $this->cors()
			]
		);
	}

	public function url(): string
	{
		// get the uri object for the Panel url
		$uri = new Uri($this->kirby->url('panel'));

		return $uri->path()->toString(true) . '/';
	}
}
