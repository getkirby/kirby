<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileBlueprint::class)]
class FileBlueprintTest extends TestCase
{
	public static function acceptAttributeProvider()
	{
		return [
			'wildcard' => [ // case name
				'image/*', // accept option in blueprint
				['.jpg', '.jpeg', '.gif', '.png'], // expected extensions
				['.js', '.pdf', '.docx', '.zip'] // not expected extensions
			],
			'mimeAsString' => [
				'image/jpeg, image/png',
				['.jpg', '.jpeg', '.png'],
				['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'mimeAsProperty' => [
				['mime' => 'image/jpeg, image/png'],
				['.jpg', '.jpeg', '.png'],
				['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'extensions' => [
				['extension' => 'jpg, png'],
				['.jpg', '.png'],
				['.gif', '.jpeg', '.js', '.pdf', '.docx', '.zip']
			],
			'extensionsAndMime' => [
				['extension' => 'foo, bar', 'mime' => 'image/jpeg, image/png'],
				['.jpg', '.jpeg', '.png'],
				['.gif', '.js', '.pdf', '.docx', '.zip', '.foo', '.bar']
			],
			'type' => [
				['type' => 'image'],
				['.jpg', '.jpeg', '.gif', '.png'],
				['.js', '.pdf', '.docx', '.zip']
			],
			'typeAndMime' => [
				['type' => 'document', 'mime' => 'image/jpeg, image/png'],
				['.jpg', '.jpeg', '.png'],
				['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'intersect' => [
				['type' => 'image', 'extension' => 'jpg, png, foo, bar'],
				['.jpg', '.png'],
				['.gif', '.js', '.pdf', '.docx', '.zip', '.foo', '.bar']
			],
		];
	}

	public function tearDown(): void
	{
		unset(Blueprint::$loaded['files/acceptAttribute']);
	}

	#[DataProvider('acceptAttributeProvider')]
	public function testAcceptAttribute(string|array $accept, array $expected, array $notExpected)
	{
		Blueprint::$loaded['files/acceptAttribute'] = [
			'accept' => $accept
		];

		$file = new File([
			'filename' => 'tmp',
			'parent'   => $this->createMock(Page::class),
			'template' => 'acceptAttribute'
		]);

		$acceptAttribute = $file->blueprint()->acceptAttribute();

		foreach ($expected as $extension) {
			$this->assertStringContainsString($extension, $acceptAttribute);
		}

		foreach ($notExpected as $extension) {
			$this->assertStringNotContainsString($extension, $acceptAttribute);
		}
	}
}
