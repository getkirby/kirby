<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileBlueprint::class)]
class FileBlueprintTest extends TestCase
{
	public function tearDown(): void
	{
		Blueprint::$loaded = [];
	}

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

	#[DataProvider('acceptAttributeProvider')]
	public function testAcceptAttribute($accept, $expected, $notExpected): void
	{
		Blueprint::$loaded['files/acceptAttribute'] = [
			'accept' => $accept
		];

		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'tmp',
			'parent'   => $page,
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

	public function testOptions(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$blueprint = new FileBlueprint([
			'model' => new File(['filename' => 'test.jpg', 'parent' => $page])
		]);

		$expected = [
			'access' 	 	 => null,
			'changeName' 	 => null,
			'changeTemplate' => null,
			'create'     	 => null,
			'delete'     	 => null,
			'list'     	 	 => null,
			'read'       	 => null,
			'replace'    	 => null,
			'sort'           => null,
			'update'     	 => null,
		];

		$this->assertSame($expected, $blueprint->options());
	}

	public function testTemplateFromContent(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'template' => 'gallery'
			]
		]);

		$this->assertSame('gallery', $file->template());
	}

	public function testCustomTemplate(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'gallery'
		]);

		$this->assertSame('gallery', $file->template());
	}

	public function testDefaultBlueprint(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'does-not-exist',
		]);

		$blueprint = $file->blueprint();

		$this->assertInstanceOf(FileBlueprint::class, $blueprint);
	}

	public function testCustomBlueprint(): void
	{
		new App([
			'blueprints' => [
				'files/gallery' => [
					'name'  => 'gallery',
					'title' => 'Gallery',
				]
			]
		]);

		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'gallery',
		]);

		$blueprint = $file->blueprint();

		$this->assertInstanceOf(FileBlueprint::class, $blueprint);
		$this->assertSame('Gallery', $blueprint->title());
	}

	public function testAccept(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		// string = MIME types
		$blueprint = new FileBlueprint([
			'accept' => 'image/jpeg, text/*',
			'model'  => $file
		]);
		$this->assertSame([
			'extension'   => null,
			'mime'        => ['image/jpeg', 'text/*'],
			'maxheight'   => null,
			'maxsize'     => null,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => null
		], $blueprint->accept());

		// empty value = no restrictions
		$expected = [
			'extension'   => null,
			'mime'        => null,
			'maxheight'   => null,
			'maxsize'     => null,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => null
		];

		$blueprint = new FileBlueprint([
			'accept' => true,
			'model'  => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => [
				'mime' => null
			],
			'model' => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => [
				'extension' => null
			],
			'model' => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => [
				'type' => null
			],
			'model' => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => [
				'mime' => null,
				'type' => null
			],
			'model' => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		// no value = default type restriction
		$expected = [
			'extension'   => null,
			'mime'        => null,
			'maxheight'   => null,
			'maxsize'     => null,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => ['image', 'document', 'archive', 'audio', 'video']
		];

		$blueprint = new FileBlueprint([
			'model' => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => null,
			'model'  => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		$blueprint = new FileBlueprint([
			'accept' => [],
			'model'  => $file
		]);
		$this->assertSame($expected, $blueprint->accept());

		// array with mixed case
		$blueprint = new FileBlueprint([
			'accept' => [
				'extensION' => ['txt'],
				'MiMe'      => ['image/jpeg', 'text/*'],
				'MAXsize'   => 100,
				'typE'      => ['document']
			],
			'model' => $file
		]);
		$this->assertSame([
			'extension'   => ['txt'],
			'mime'        => ['image/jpeg', 'text/*'],
			'maxheight'   => null,
			'maxsize'     => 100,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => ['document']
		], $blueprint->accept());

		// MIME, extension and type normalization
		$blueprint = new FileBlueprint([
			'accept' => [
				'mime'      => 'image/jpeg,  image/png;q=0.7',
				'extension' => 'txt,json  ,  jpg',
				'type'      => 'document;audio  ,  video'
			],
			'model' => $file
		]);
		$this->assertSame([
			'extension'   => ['txt', 'json', 'jpg'],
			'mime'        => ['image/jpeg', 'image/png'],
			'maxheight'   => null,
			'maxsize'     => null,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => ['document;audio', 'video']
		], $blueprint->accept());
	}

	public function testAcceptMime(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		// default restrictions
		$blueprint = new FileBlueprint([
			'model'  => $file
		]);
		$this->assertSame('*', $blueprint->acceptMime());

		// no restrictions
		$blueprint = new FileBlueprint([
			'accept' => true,
			'model'  => $file
		]);
		$this->assertSame('*', $blueprint->acceptMime());

		// just MIME restrictions
		$blueprint = new FileBlueprint([
			'accept' => 'image/jpeg,  image/png;q=0.7',
			'model'  => $file
		]);
		$this->assertSame('image/jpeg, image/png', $blueprint->acceptMime());

		// just extension restrictions
		$blueprint = new FileBlueprint([
			'accept' => [
				'extension' => 'jpg, mp4'
			],
			'model' => $file
		]);
		$this->assertSame('image/jpeg, video/mp4', $blueprint->acceptMime());

		// just type restrictions
		$blueprint = new FileBlueprint([
			'accept' => [
				'type' => 'archive, audio'
			],
			'model' => $file
		]);
		$this->assertSame(
			'application/x-gzip, application/x-tar, application/x-zip, ' .
			'audio/x-aiff, audio/mp4, audio/midi, audio/mpeg, audio/wav',
			$blueprint->acceptMime()
		);

		// combined extension and type restrictions
		$blueprint = new FileBlueprint([
			'accept' => [
				'extension' => 'jpg, txt, png',
				'type'      => 'image, audio'
			],
			'model' => $file
		]);
		$this->assertSame('image/jpeg, image/png', $blueprint->acceptMime());

		// don't override explicit MIME types with other restrictions
		$blueprint = new FileBlueprint([
			'accept' => [
				'mime'      => 'image/jpeg,  application/pdf;q=0.7',
				'extension' => 'jpg, txt, png',
				'type'      => 'document, image'
			],
			'model' => $file
		]);
		$this->assertSame('image/jpeg, application/pdf', $blueprint->acceptMime());
	}

	public function testExtendAccept(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'files/base' => [
					'name'  => 'base',
					'title' => 'Base',
					'accept' => [
						'mime' => 'image/jpeg'
					]
				],
				'files/image' => [
					'name'    => 'image',
					'title'   => 'Image',
					'extends' => 'files/base'
				]
			]
		]);

		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'image',
		]);

		$blueprint = $file->blueprint();
		$this->assertSame(['image/jpeg'], $blueprint->accept()['mime']);
	}
}
