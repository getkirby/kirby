<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePicker::class)]
class NewFilePickerTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFilePicker';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				],
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'd.jpg'],
							['filename' => 'e.jpg']
						]
					]
				],
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'f.jpg']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testDefaults(): void
	{
		$picker = new FilePicker();

		$this->assertCount(3, $picker->items());
	}

	public function testQuery(): void
	{
		$picker = new FilePicker([
			'query' => 'site.files.offset(1)'
		]);

		$this->assertCount(2, $picker->items());
	}

	public function testQuerySite(): void
	{
		$picker = new FilePicker([
			'query' => 'site'
		]);

		$this->assertCount(3, $picker->items());
	}

	public function testQueryPage(): void
	{
		$picker = new FilePicker([
			'query' => 'kirby.page("test")'
		]);

		$this->assertCount(2, $picker->items());
	}

	public function testQueryUser(): void
	{
		$picker = new FilePicker([
			'query' => 'kirby.user("test")'
		]);

		$this->assertCount(1, $picker->items());
	}

	public function testQueryFile(): void
	{
		$picker = new FilePicker([
			'model' => $this->app->site()->files()->first()
		]);

		$this->assertCount(3, $picker->items());
	}

	public function testQueryInvalid(): void
	{
		$picker = new FilePicker([
			'query' => 'site.pages'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Your query must return a set of files');

		$picker->items();
	}
}
