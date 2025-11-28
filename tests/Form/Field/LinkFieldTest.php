<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkField::class)]
class LinkFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('link');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'default'     => null,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'label'       => 'Link',
			'name'        => 'link',
			'options'     => ['url', 'page', 'file', 'email', 'tel', 'anchor'],
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'link',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testActiveTypes(): void
	{
		$field = $this->field('link', [
			'options' => ['page', 'file', 'email']
		]);

		$active = $field->activeTypes();
		$this->assertSame(['email', 'file', 'page'], array_keys($active));
	}

	public function testAvailableTypes(): void
	{
		$field = $this->field('link');

		$available = $field->availableTypes();
		$this->assertSame(
			['anchor', 'email', 'file', 'page', 'tel', 'url', 'custom'],
			array_keys($available)
		);

		// anchor
		$type = $available['anchor'];
		$this->assertTrue($type['detect']('#section-1'));
		$this->assertFalse($type['detect']('section-1'));
		$this->assertSame('#section-1', $type['link']('#section-1'));
		$this->assertTrue($type['validate']('#section-1'));
		$this->assertFalse($type['validate']('section-1'));

		// email
		$type = $available['email'];
		$this->assertTrue($type['detect']('mailto:test@getkirby.com'));
		$this->assertFalse($type['detect']('test@getkirby.com'));
		$this->assertSame('test@getkirby.com', $type['link']('mailto:test@getkirby.com'));
		$this->assertTrue($type['validate']('test@getkirby.com'));
		$this->assertFalse($type['validate']('test@getkirby'));

		// file
		$type = $available['file'];
		$this->assertTrue($type['detect']('file://my-file'));
		$this->assertFalse($type['detect']('page://my-file'));
		$this->assertSame('file://my-file', $type['link']('file://my-file'));
		$this->assertTrue($type['validate']('file://my-file'));
		$this->assertFalse($type['validate']('file:/my-file'));

		// page
		$type = $available['page'];
		$this->assertTrue($type['detect']('page://my-page'));
		$this->assertFalse($type['detect']('user://my-page'));
		$this->assertSame('page://my-page', $type['link']('page://my-page'));
		$this->assertTrue($type['validate']('page://my-page'));
		$this->assertFalse($type['validate']('page:/my-page'));

		// tel
		$type = $available['tel'];
		$this->assertTrue($type['detect']('tel:1234567890'));
		$this->assertFalse($type['detect']('phone:1234567890'));
		$this->assertSame('1234567890', $type['link']('tel:1234567890'));
		$this->assertTrue($type['validate']('1234567890'));
		$this->assertFalse($type['validate']('abc123456'));

		// url
		$type = $available['url'];
		$this->assertTrue($type['detect']('https://getkirby.com'));
		$this->assertTrue($type['detect']('http://getkirby.com'));
		$this->assertFalse($type['detect']('ftp://getkirby.com'));
		$this->assertSame('https://getkirby.com', $type['link']('https://getkirby.com'));
		$this->assertTrue($type['validate']('https://getkirby.com'));
		$this->assertFalse($type['validate']('getkirby'));

		// custom
		$type = $available['custom'];
		$this->assertTrue($type['detect']('abc'));
		$this->assertSame('abc', $type['link']('abc'));
		$this->assertTrue($type['validate']('abc'));
	}

	public function testOptions(): void
	{
		$field = $this->field('link', [
			'options' => ['page', 'email', 'tel']
		]);

		$this->assertSame(['page', 'email', 'tel'], $field->options());

		// default options
		$field = $this->field('link');

		$this->assertSame(['url', 'page', 'file', 'email', 'tel', 'anchor'], $field->options());
	}

	public function testOptionsInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid options: foo, bar');

		$field = $this->field('link', [
			'options' => ['page', 'foo', 'bar']
		]);
		$field->options();
	}

	public function testValidations(): void
	{
		$field = $this->field('link');

		$validations = $field->validations();
		$validation  = $validations['option'];

		$validation(null);
		$validation('http://getkirby.com');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The link type is not allowed');
		$validation('abc:/!');
	}
}
