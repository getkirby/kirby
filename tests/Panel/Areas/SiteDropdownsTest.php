<?php

namespace Kirby\Panel\Areas;

class SiteDropdownsTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testPageDropdown(): void
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->login();

		$options = $this->dropdown('pages/test')['options'];

		$title = $options[0];
		$this->assertSame([
			'url'   => '/pages/test/changeTitle',
			'query' => [
				'select' => 'title'
			]
		], $title['dialog']);
		$this->assertSame('Rename', $title['text']);

		$slug = $options[1];
		$this->assertSame([
			'url'   => '/pages/test/changeTitle',
			'query' => [
				'select' => 'slug'
			]
		], $slug['dialog']);
		$this->assertSame('Change URL', $slug['text']);

		$status = $options[2];
		$this->assertSame('/pages/test/changeStatus', $status['dialog']);
		$this->assertSame('Change status', $status['text']);

		$position = $options[3];
		$this->assertSame('/pages/test/changeSort', $position['dialog']);
		$this->assertSame('Change position', $position['text']);

		$template = $options[4];
		$this->assertSame('/pages/test/changeTemplate', $template['dialog']);
		$this->assertSame('Change template', $template['text']);

		$this->assertSame('-', $options[5]);

		$move = $options[6];
		$this->assertSame('/pages/test/move', $move['dialog']);
		$this->assertSame('Move page', $move['text']);

		$duplicate = $options[7];
		$this->assertSame('/pages/test/duplicate', $duplicate['dialog']);
		$this->assertSame('Duplicate', $duplicate['text']);

		$this->assertSame('-', $options[8]);

		$delete = $options[9];
		$this->assertSame('/pages/test/delete', $delete['dialog']);
		$this->assertSame('Delete', $delete['text']);
	}

	public function testPageDropdownInListView(): void
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'request' => [
				'query' => [
					'view' => 'list'
				]
			]
		]);

		$this->login();

		$options = $this->dropdown('pages/test')['options'];

		$preview = $options[0];

		$this->assertSame('/test', $preview['link']);
		$this->assertSame('_blank', $preview['target']);
		$this->assertSame('Open', $preview['text']);
	}

	public function testPageLanguageDropdown()
	{
		$this->app([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'languages' => [
				'en' => [
					'code' => 'en',
					'name' => 'English',
				],
				'de' => [
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		$this->login();
		$this->assertLanguageDropdown('pages/test/languages');
	}

	public function testSiteLanguageDropdown()
	{
		$this->app([
			'languages' => [
				'en' => [
					'code' => 'en',
					'name' => 'English',
				],
				'de' => [
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		$this->login();
		$this->assertLanguageDropdown('site/languages');
	}
}
