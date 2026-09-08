<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileAbilities::class)]
class FileAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.FileAbilities';

	public function testChangeTemplateWithMultipleBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'list-a' => [
							'type'     => 'filelist',
							'template' => 'for-list/a'
						],
						'list-b' => [
							'type'     => 'filelist',
							'template' => 'for-list/b'
						]
					]
				],
				'files/for-list/a' => [
					'title' => 'Type A'
				],
				'files/for-list/b' => [
					'title' => 'Type B'
				]
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$abilities = new FileAbilities(
			model: $file,
			user: $this->user()
		);

		$this->assertGreaterThan(1, count($file->blueprints()));
		$this->assertNull($abilities->ensure('changeTemplate'));
	}

	public function testChangeTemplateWithSingleBlueprint(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$abilities = new FileAbilities(
			model: $file,
			user: $this->user()
		);

		$this->assertCount(1, $file->blueprints());

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.file.changeTemplate');

		$abilities->ensure('changeTemplate');
	}

	public function testError(): void
	{
		$abilities = $this->abilities();

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.file.test');

		$abilities->error('test');
	}

	protected function abilities(): FileAbilities
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => new Page(['slug' => 'test'])
		]);

		return new FileAbilities(
			model: $file,
			user: $this->user()
		);
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}
}
