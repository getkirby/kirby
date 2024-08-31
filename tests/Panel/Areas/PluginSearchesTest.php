<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;

class PluginSearchesTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
		App::destroy();
	}

	public function testLegacyPluginSearch(): void
	{
		App::plugin('test/a', [
			'areas' => [
				'test' => [
					'search' => 'test',
					'searches' => [
						'test' => [
							'query' => fn (string|null $query = null) => [['a'], ['b'], ['c']]
						]
					]
				]
			]
		]);

		$this->app([
			'request' => [
				'query' => [
					'query' => 'test'
				]
			]
		]);

		$this->login();

		$search = $this->search('test');

		$this->assertCount(3, $search['results']);
		$this->assertSame(1, $search['pagination']['page']);
		$this->assertSame(3, $search['pagination']['limit']);
		$this->assertSame(3, $search['pagination']['total']);
	}
}
