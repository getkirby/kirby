<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

class PageGuardsTestValidators extends PageValidators
{
	protected function ensureToChangeStatus(
		string $status,
		int|null $position = null
	): void {
		// skips the status validation on purpose, so that an
		// unknown status reaches the guards themselves
	}
}

class PageGuardsTestGuards extends PageGuards
{
	public function __construct(
		Page $model,
		User $user
	) {
		parent::__construct(
			model: $model,
			user: $user
		);

		$this->validators = new PageGuardsTestValidators(
			model: $model,
			user: $user
		);
	}
}

#[CoversClass(PageGuards::class)]
class PageGuardsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.PageGuards';

	protected function guards(Page|null $page = null): PageGuards
	{
		return new PageGuards(
			model: $page ?? new Page(['slug' => 'test']),
			user: $this->user()
		);
	}

	protected function movable(): Page
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'sections' => [
						'subpages' => [
							'type'     => 'pages',
							'template' => 'child'
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'template' => 'parent',
						'children' => [
							[
								'slug'     => 'child',
								'template' => 'child'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'template' => 'parent'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');

		return $this->app->page('parent-a/child');
	}

	protected function user(): User
	{
		return $this->app->user() ?? new User(['id' => 'test']);
	}

	public function testChangeNum(): void
	{
		$this->assertNull($this->guards()->ensureExecutable('changeNum', 1));
	}

	public function testChangeNumWithInvalidNum(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$this->guards()->ensureExecutable('changeNum', -1);
	}

	public function testChangeStatusToDraft(): void
	{
		$this->assertNull(
			$this->guards()->ensureExecutable('changeStatus', 'draft')
		);
	}

	public function testChangeStatusToDraftWithHomePage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'home']]
			]
		]);

		$this->app->impersonate('kirby');

		// the home page has no draft status in its blueprint, so the
		// action can only be reached directly and not via `changeStatus`
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.toDraft.homePage');

		$this->guards($this->app->page('home'))->ensureExecutable('changeStatusToDraft');
	}

	public function testChangeStatusToListed(): void
	{
		$this->assertNull(
			$this->guards()->ensureExecutable('changeStatus', 'listed', 1)
		);
	}

	public function testChangeStatusToListedWithInvalidPosition(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$this->guards()->ensureExecutable('changeStatus', 'listed', -1);
	}

	public function testChangeStatusToListedWithListedPage(): void
	{
		// a page that is already listed is only re-sorted,
		// which needs the sort rules instead of the publish rules
		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertNull(
			$this->guards($page)->ensureExecutable('changeStatus', 'listed', 2)
		);
	}

	public function testChangeStatusToListedWithUnsortablePage(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/date' => ['num' => 'date']
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'     => 'test',
			'num'      => 1,
			'template' => 'date'
		]);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.sort.mode');

		$this->guards($page)->ensureExecutable('changeStatus', 'listed', 2);
	}

	public function testChangeStatusToUnlisted(): void
	{
		$this->assertNull(
			$this->guards()->ensureExecutable('changeStatus', 'unlisted')
		);
	}

	public function testChangeStatusWithInvalidStatus(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.status.invalid');

		$this->guards()->ensureExecutable('changeStatus', 'does-not-exist');
	}

	public function testChangeStatusWithUnvalidatedStatus(): void
	{
		// the validators would normally reject an unknown status,
		// but the guards must not fall through to any of the
		// three status branches either
		$guards = new PageGuardsTestGuards(
			model: new Page(['slug' => 'test']),
			user: $this->user()
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.status.invalid');

		$guards->ensureExecutable('changeStatus', 'does-not-exist');
	}

	public function testCreate(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->assertNull($this->guards($page)->ensureExecutable('create'));
	}

	public function testCreateWithDuplicate(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'test']]
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->expectExceptionCode('error.page.duplicate');

		$this->guards($page)->ensureExecutable('create');
	}

	public function testCreateWithPublishedPage(): void
	{
		// creating a page that is not a draft bypasses the
		// normal publish flow and must run its rules as well
		$page = new Page(['slug' => 'test']);

		$this->assertNull($this->guards($page)->ensureExecutable('create'));
	}

	public function testMove(): void
	{
		$page = $this->movable();

		$this->assertNull(
			$this->guards($page)->ensureExecutable('move', $this->app->page('parent-b'))
		);
	}

	public function testMoveToSameParent(): void
	{
		$page = $this->movable();

		// nothing changes, so nothing needs to be checked
		$this->assertNull(
			$this->guards($page)->ensureExecutable('move', $this->app->page('parent-a'))
		);
	}

	public function testMoveWithHomePage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home'],
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.move.homePage');

		$this->guards($this->app->page('home'))->ensureExecutable('move', $this->app->page('test'));
	}

	public function testPublish(): void
	{
		$this->assertNull($this->guards()->ensureExecutable('publish'));
	}

	public function testPublishWithErrorPage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'error']]
			]
		]);

		$this->app->impersonate('kirby');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.changeStatus.errorPage');

		$this->guards($this->app->page('error'))->ensureExecutable('publish');
	}
}
