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
	/**
	 * Renders the panel document
	 */
	public static function response(array $fiber): Response
	{
		$kirby  = App::instance();
		$assets = new Assets();

		// Full HTML response
		// @codeCoverageIgnoreStart
		try {
			if ($assets->link() === true) {
				usleep(1);
				Response::go($kirby->url('base') . '/' . $kirby->path());
			}
		} catch (Throwable $e) {
			die('The Panel assets cannot be installed properly. ' . $e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// get the uri object for the panel url
		$uri = new Uri($kirby->url('panel'));

		// proper response code
		$code = $fiber['$view']['code'] ?? 200;

		// load the main Panel view template
		$body = Tpl::load($kirby->root('kirby') . '/views/panel.php', [
			'assets'   => $assets->external(),
			'icons'    => $assets->icons(),
			'nonce'    => $kirby->nonce(),
			'fiber'    => $fiber,
			'panelUrl' => $uri->path()->toString(true) . '/',
		]);

		$frameAncestors = $kirby->option('panel.frameAncestors');
		$frameAncestors = match (true) {
			$frameAncestors === true   => "'self'",
			is_array($frameAncestors)  => "'self' " . implode(' ', $frameAncestors),
			is_string($frameAncestors) => $frameAncestors,
			default                    => "'none'"
		};

		return new Response($body, 'text/html', $code, [
			'Content-Security-Policy' => 'frame-ancestors ' . $frameAncestors
		]);
	}
}
