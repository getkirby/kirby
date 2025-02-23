<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteSearchTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteSearch';

	public function testSearch(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => ['title' => 'Mountainbike']
				],
				[
					'slug'    => 'mountains',
					'content' => ['title' => 'Mountains']
				],
				[
					'slug'    => 'lakes',
					'content' => ['title' => 'Lakes']
				]
			]
		]);

		$result = $site->search('mountain');
		$this->assertCount(2, $result);

		$result = $site->search('mountain', 'title|text');
		$this->assertCount(2, $result);

		$result = $site->search('mountain', 'text');
		$this->assertCount(0, $result);
	}

	public function testSearchMinlength(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'foo-a'],
				['slug' => 'bar-b'],
			]
		]);

		$collection = $site->search('foo', [
			'minlength' => 5
		]);

		$this->assertCount(0, $collection);

		$collection = $site->search('foo', [
			'minlength' => 1
		]);

		$this->assertCount(2, $collection);
	}

	public function testSearchWords(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug'    => 'mtb',
					'content' => ['title' => 'Mountainbike']
				],
				[
					'slug'    => 'mountain',
					'content' => ['title' => 'Mountain']
				],
				[
					'slug'    => 'everest-mountain',
					'content' => ['title' => 'Everest Mountain']
				],
				[
					'slug'    => 'mount',
					'content' => ['title' => 'Mount']
				],
				[
					'slug'    => 'lakes',
					'content' => ['title' => 'Lakes']
				]
			]
		]);

		$result = $site->search('mountain', ['words' => true]);
		$this->assertCount(2, $result);

		$result = $site->search('mount', ['words' => false]);
		$this->assertCount(4, $result);
	}

	public function testSearchStopWords(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'baz'],
				['slug' => 'foo-bar'],
				['slug' => 'foo-baz'],
			]
		]);

		$collection = $site->search('foo bar', [
			'stopwords' => ['bar']
		]);

		$this->assertCount(3, $collection);
	}

	public function testSearchStopWordsNoResults(): void
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'baz'],
				['slug' => 'foo-bar'],
				['slug' => 'foo-baz'],
			]
		]);

		$collection = $site->search('new foo', [
			'stopwords' => ['foo']
		]);

		$this->assertCount(0, $collection);
	}
}
