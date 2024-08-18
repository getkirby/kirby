<?php

namespace Kirby\Email;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Email\Body
 * @covers ::__construct
 */
class BodyTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::factory
	 */
	public function testFactory(): void
	{
		$body = Body::factory($text = 'test');
		$this->assertSame($text, $body->text());
		$this->assertSame('', $body->html());

		$body = Body::factory([
			'text' => $text,
			'html' => $html = '<b>test</b>'
		]);
		$this->assertSame($text, $body->text());
		$this->assertSame($html, $body->html());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryTemplate(): void
	{
		new App([
			'templates' => [
				'emails/contact' => static::FIXTURES . '/contact.php'
			]
		]);

		$body = Body::factory(null, 'contact', ['name' => 'Alex']);
		$this->assertSame('Cheers, Alex!', $body->text());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryTemplateHtml(): void
	{
		new App([
			'templates' => [
				'emails/media.html' => static::FIXTURES . '/media.html.php'
			]
		]);

		$body = Body::factory(null, 'media');
		$this->assertSame('<b>Image:</b> <img src=""/>', $body->html());
		$this->assertSame('', $body->text());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryTemplateHtmlText(): void
	{
		new App([
			'templates' => [
				'emails/media.html' => static::FIXTURES . '/media.html.php',
				'emails/media.text' => static::FIXTURES . '/media.text.php',
			]
		]);

		$body = Body::factory(null, 'media');
		$this->assertSame('<b>Image:</b> <img src=""/>', $body->html());
		$this->assertSame('Image: Description', $body->text());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryTemplateData(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'templates' => [
				'emails/user-info' => static::FIXTURES . '/user-info.php'
			]
		]);

		$user = new User([
			'email' => 'ceo@company.com',
			'name'  => 'Mario'
		]);

		$body = Body::factory(null, 'user-info', ['user' => $user]);
		$this->assertSame('Welcome, Mario!', trim($body->text()));
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryTemplateInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The email template "subscription" cannot be found');
		Body::factory(null, 'subscription');
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Email requires either body or template');
		Body::factory();
	}

	/**
	 * @covers ::html
	 */
	public function testHtml(): void
	{
		$body = new Body();
		$this->assertSame('', $body->html());

		$body = new Body(html: $html = '<strong>Foo</strong>');
		$this->assertSame($html, $body->html());
	}

	/**
	 * @covers ::isHtml
	 */
	public function testIsHtml(): void
	{
		$body = new Body();
		$this->assertFalse($body->isHtml());

		$body = new Body(html: '<strong>Foo</strong>');
		$this->assertTrue($body->isHtml());
	}

	/**
	 * @covers ::text
	 */
	public function testText(): void
	{
		$body = new Body();
		$this->assertSame('', $body->text());

		$body = new Body(text: $text = 'Foo');
		$this->assertSame($text, $body->text());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray(): void
	{
		$body = new Body(
			text: 'test',
			html: '<b>test</b>'
		);

		$this->assertSame([
			'html' => '<b>test</b>',
			'text' => 'test'
		], $body->toArray());
	}
}
