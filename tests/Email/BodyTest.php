<?php

namespace Kirby\Email;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Body::class)]
class BodyTest extends TestCase
{
	public function testConstruct(): void
	{
		$body = new Body();

		$this->assertSame('', $body->html());
		$this->assertSame('', $body->text());
	}

	public function testConstructParams(): void
	{
		$data = [
			'html' => '<strong>We will never reply</strong>',
			'text' => 'We will never reply'
		];

		$body = new Body($data);

		$this->assertSame($data['html'], $body->html());
		$this->assertSame($data['text'], $body->text());
	}

	public function testConstructNullParams(): void
	{
		$body = new Body([
			'html' => null,
			'text' => null
		]);

		$this->assertSame('', $body->html());
		$this->assertSame('', $body->text());
	}
}
