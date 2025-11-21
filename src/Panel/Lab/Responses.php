<?php

namespace Kirby\Panel\Lab;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;

/**
 * Example responses for test requests
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.2.0
 * @internal
 * @codeCoverageIgnore
 */
class Responses
{
	public static function errorResponseByType(string|null $type = null): Response|Exception
	{
		return match ($type) {
			'form' => new InvalidArgumentException(
				fallback: 'Exception with details',
				details: [
					'a' => [
						'label'   => 'Detail A',
						'message' => [
							'This is a message for Detail A',
						],
					],
					'b' => [
						'label'   => 'Detail B',
						'message' => [
							'This is the first message for Detail B',
							'This is the second message for Detail B',
						],
					],
				]
			),
			'html'         => new Response('<h1>Hello</h1>', 'html'),
			'invalid-json' => new Response('invalid json', 'json'),
			default        => new Exception('This is a custom backend error'),
		};
	}
}
