<?php

namespace Kirby\Panel\Ui;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Upload::class)]
class UploadTest extends TestCase
{
	public function testAcceptWithoutValue(): void
	{
		$upload = new Upload(
			api: '/test'
		);

		$this->assertNull($upload->props()['accept']);
	}

	public function testAcceptWithValue(): void
	{
		$upload = new Upload(
			api: '/test',
			accept: 'image/*',
		);

		$this->assertSame('image/*', $upload->props()['accept']);
	}

	public function testApi(): void
	{
		$upload = new Upload(
			api: '/test'
		);

		$this->assertSame('/test', $upload->props()['api']);
	}

	public function testAttributes(): void
	{
		$upload = new Upload(
			api: '/test'
		);

		$this->assertSame(['sort' => null, 'template' => null], $upload->props()['attributes']);
	}

	public function testAttributesWithCustomAttributes(): void
	{
		$upload = new Upload(
			api: '/test',
			attributes: ['foo' => 'bar']
		);

		$this->assertSame([
			'foo'      => 'bar',
			'sort'     => null,
			'template' => null,
		], $upload->props()['attributes']);
	}

	public function testAttributesWithSort(): void
	{
		$upload = new Upload(
			api: '/test',
			sort: 5
		);

		$this->assertSame(5, $upload->props()['attributes']['sort']);
	}

	public function testAttributesWithTemplate(): void
	{
		$upload = new Upload(
			api: '/test',
			template: 'image'
		);

		$this->assertSame('image', $upload->props()['attributes']['template']);
	}

	public function testAttributesWithDefaultTemplate(): void
	{
		$upload = new Upload(
			api: '/test',
			template: 'default'
		);

		$this->assertNull($upload->props()['attributes']['template']);
	}

	public function testMaxWithoutValue(): void
	{
		$upload = new Upload(
			api: '/test'
		);

		$this->assertNull($upload->props()['max']);
	}

	public function testMaxWithValue(): void
	{
		$upload = new Upload(
			api: '/test',
			max: 10
		);

		$this->assertSame(10, $upload->props()['max']);
	}

	public function testMaxWithMultipleFalse(): void
	{
		$upload = new Upload(
			api: '/test',
			max: 10,
			multiple: false
		);

		$this->assertSame(1, $upload->props()['max']);
	}

	public function testMultipleWithoutValue(): void
	{
		$upload = new Upload(
			api: '/test'
		);

		$this->assertTrue($upload->props()['multiple']);
	}

	public function testMultipleWithValue(): void
	{
		$upload = new Upload(
			api: '/test',
			multiple: false
		);

		$this->assertFalse($upload->props()['multiple']);
	}

	public function testMultipleWithMaxOne(): void
	{
		$upload = new Upload(
			api: '/test',
			multiple: true,
			max: 1
		);

		$this->assertFalse($upload->props()['multiple']);
	}

	public function testProps(): void
	{
		$upload = new Upload(
			api: '/test',
			accept: 'image/*',
			attributes: ['foo' => 'bar'],
			max: 10,
			multiple: true,
			preview: [
				'icon' => 'image'
			],
			sort: 5,
			template: 'image'
		);

		$this->assertSame([
			'accept'     => 'image/*',
			'api'        => '/test',
			'attributes' => [
				'foo'      => 'bar',
				'sort'     => 5,
				'template' => 'image',
			],
			'max'        => 10,
			'multiple'   => true,
			'preview'    => [
				'icon' => 'image'
			],
		], $upload->props());
	}
}
