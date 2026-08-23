<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageAbilities::class)]
class PageAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.PageAbilities';

	protected function abilities(string $slug): PageAbilities
	{
		return new PageAbilities(
			model: new Page(['slug' => $slug]),
			user: $this->user()
		);
	}

	public function testChangeSlug(): void
	{
		$this->assertNull($this->abilities('test')->ensure('changeSlug'));
	}

	public function testChangeSlugWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeSlug.errorPage');

		$this->abilities('error')->ensure('changeSlug');
	}

	public function testChangeSlugWithHomePage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeSlug.homePage');

		$this->abilities('home')->ensure('changeSlug');
	}

	public function testChangeStatus(): void
	{
		$this->assertNull($this->abilities('test')->ensure('changeStatus'));
		$this->assertNull($this->abilities('home')->ensure('changeStatus'));
	}

	public function testChangeStatusToDraft(): void
	{
		$this->assertNull($this->abilities('test')->ensure('changeStatusToDraft'));
	}

	public function testChangeStatusToDraftWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.toDraft.errorPage');

		$this->abilities('error')->ensure('changeStatusToDraft');
	}

	public function testChangeStatusToDraftWithHomePage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.toDraft.homePage');

		$this->abilities('home')->ensure('changeStatusToDraft');
	}

	public function testChangeStatusWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.errorPage');

		$this->abilities('error')->ensure('changeStatus');
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

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeTemplate.errorPage');

		$abilities->ensure('changeTemplate');
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

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->assertCount(2, $page->blueprints());
		$this->assertNull($abilities->ensure('changeTemplate'));
	}

	public function testChangeTemplateWithSingleBlueprint(): void
	{
		$page      = new Page(['slug' => 'test']);
		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->assertCount(1, $page->blueprints());

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeTemplate.invalid');

		$abilities->ensure('changeTemplate');
	}

	public function testDelete(): void
	{
		$this->assertNull($this->abilities('test')->ensure('delete'));
	}

	public function testDeleteWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.delete.errorPage');

		$this->abilities('error')->ensure('delete');
	}

	public function testDeleteWithHomePage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.delete.homePage');

		$this->abilities('home')->ensure('delete');
	}

	public function testMove(): void
	{
		$this->assertNull($this->abilities('test')->ensure('move'));
	}

	public function testMoveWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.move.errorPage');

		$this->abilities('error')->ensure('move');
	}

	public function testMoveWithHomePage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.move.homePage');

		$this->abilities('home')->ensure('move');
	}

	public function testPublish(): void
	{
		$this->assertNull($this->abilities('test')->ensure('publish'));
	}

	public function testPublishWithErrorPage(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.errorPage');

		$this->abilities('error')->ensure('publish');
	}

	public function testPublishWithIncompleteDraft(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'headline' => [
							'type'     => 'text',
							'required' => true
						]
					]
				]
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test',
			'isDraft'  => true
		]);

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->assertNotSame([], $page->errors());

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$abilities->ensure('publish');
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

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.sort.mode');

		$abilities->ensure('sort');
	}

	public function testSortWithErrorPage(): void
	{
		$page = new Page([
			'slug' => 'error',
			'num'  => 1
		]);

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.sort.errorPage');

		$abilities->ensure('sort');
	}

	public function testSortWithListedPage(): void
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->assertNull($abilities->ensure('sort'));
	}

	public function testSortWithUnlistedPage(): void
	{
		$page = new Page(['slug' => 'test']);

		$abilities = new PageAbilities(
			model: $page,
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.sort.unlisted');

		$abilities->ensure('sort');
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}
}
