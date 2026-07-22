<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageAbilities::class)]
class PageAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageAbilities';

	protected function abilities(string $slug): PageAbilities
	{
		return new PageAbilities(new Page(['slug' => $slug]));
	}

	public function testChangeSlug(): void
	{
		$this->assertTrue($this->abilities('test')->changeSlug());
		$this->assertFalse($this->abilities('home')->changeSlug());
		$this->assertFalse($this->abilities('error')->changeSlug());
	}

	public function testChangeStatus(): void
	{
		$this->assertTrue($this->abilities('test')->changeStatus());
		$this->assertTrue($this->abilities('home')->changeStatus());
		$this->assertFalse($this->abilities('error')->changeStatus());
	}

	public function testChangeStatusToDraft(): void
	{
		$this->assertTrue($this->abilities('test')->changeStatusToDraft());
		$this->assertFalse($this->abilities('home')->changeStatusToDraft());
		$this->assertFalse($this->abilities('error')->changeStatusToDraft());
	}

	public function testChangeTemplateWithErrorPage(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => ['title' => 'A'],
				'pages/b' => ['title' => 'B']
			]
		]);

		$page = new Page([
			'slug'     => 'error',
			'template' => 'a',
			'blueprint' => [
				'name'    => 'a',
				'options' => [
					'changeTemplate' => ['a', 'b']
				]
			]
		]);

		$abilities = new PageAbilities($page);

		$this->assertFalse($abilities->changeTemplate());
	}

	public function testChangeTemplateWithMultipleBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => ['title' => 'A'],
				'pages/b' => ['title' => 'B']
			]
		]);

		$page = new Page([
			'slug'      => 'test',
			'template'  => 'a',
			'blueprint' => [
				'name'    => 'a',
				'options' => [
					'changeTemplate' => ['a', 'b']
				]
			]
		]);

		$abilities = new PageAbilities($page);

		$this->assertCount(2, $page->blueprints());
		$this->assertTrue($abilities->changeTemplate());
	}

	public function testChangeTemplateWithSingleBlueprint(): void
	{
		$page      = new Page(['slug' => 'test']);
		$abilities = new PageAbilities($page);

		$this->assertCount(1, $page->blueprints());
		$this->assertFalse($abilities->changeTemplate());
	}

	public function testDelete(): void
	{
		$this->assertTrue($this->abilities('test')->delete());
		$this->assertFalse($this->abilities('home')->delete());
		$this->assertFalse($this->abilities('error')->delete());
	}

	public function testHasWithoutCheckMethod(): void
	{
		$abilities = $this->abilities('test');

		$this->assertFalse($abilities->has('changeTitle'));
		$this->assertFalse($abilities->has('update'));
	}

	public function testMove(): void
	{
		$this->assertTrue($this->abilities('test')->move());
		$this->assertFalse($this->abilities('home')->move());
		$this->assertFalse($this->abilities('error')->move());
	}

	public function testSortWithCustomNum(): void
	{
		$page = new Page([
			'slug'      => 'test',
			'num'       => 1,
			'blueprint' => [
				'name' => 'test',
				'num'  => 'date'
			]
		]);

		$abilities = new PageAbilities($page);

		$this->assertFalse($abilities->sort());
	}

	public function testSortWithErrorPage(): void
	{
		$page = new Page([
			'slug' => 'error',
			'num'  => 1
		]);

		$abilities = new PageAbilities($page);

		$this->assertFalse($abilities->sort());
	}

	public function testSortWithListedPage(): void
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$abilities = new PageAbilities($page);

		$this->assertTrue($abilities->sort());
	}

	public function testSortWithUnlistedPage(): void
	{
		$page = new Page(['slug' => 'test']);

		$abilities = new PageAbilities($page);

		$this->assertFalse($abilities->sort());
	}
}
