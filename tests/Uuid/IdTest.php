<?php

namespace Kirby\Uuid;

/**
 * @coversDefaultClass \Kirby\Uuid\Id
 */
class IdTest extends TestCase
{
	/**
	 * @covers ::generate
	 */
	public function testGenerate()
	{
		// default length
		$id = Id::generate();
		$this->assertSame(15, strlen($id));

		// custom length
		$id = Id::generate(5);
		$this->assertSame(5, strlen($id));

		// custom generator callback
		Id::$generator = fn ($length) => 'veryunique' . $length;
		$this->assertSame('veryunique13', Id::generate(13));
		Id::$generator = null;
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => [
							'uuid' => 'my-id',
							'foo' => '
-
  uuid: my-struct
'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'my-file']
							]
						]
					]
				]
			],
			'users' => [
				['id' => 'my-user']
			]
		]);

		$site   = $app->site();
		$page   = $site->find('a');
		$file   = $page->file('a.jpg');
		$user   = $app->user('my-user');
		$struct = $page->foo()->toStructure()->first();

		$this->assertSame('', Id::get($site));
		$this->assertSame('my-id', Id::get($page));
		$this->assertSame('my-file', Id::get($file));
		$this->assertSame('my-user', Id::get($user));
		$this->assertSame('my-struct', Id::get($struct));
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a'
					]
				]
			]
		]);

		$page = $app->page('a');
		$this->assertNull(Id::get($page));

		$page = Id::write($page, 'my-id');
		$this->assertSame('my-id', Id::get($page));
	}
}
