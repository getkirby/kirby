<?php

namespace Kirby\Panel\Lab;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;

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

