<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageValidators::class)]
class PageValidatorsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.PageValidators';

	public function testContent(): void
	{
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			],
			'content' => [
				'headline' => 'Headline'
			]
		]));

		$this->assertNull($validators->validateContent());
	}

	public function testContentWithCompleteTranslations(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug'      => 'test',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]);

		$page->version()->save(['headline' => 'Headline'], 'en');

		// the missing German translation falls back to the default language
		$this->assertNull($this->validators($page)->validateContent());
	}

	public function testContentWithErrors(): void
	{
		$page = new Page([
			'slug'      => 'test',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]);

		try {
			$this->validators($page)->validateContent();
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('error.page.changeStatus.incomplete', $e->getKey());
			$this->assertSame($page->errors(), $e->getDetails());
		}
	}

	public function testContentWithIncompleteTranslation(): void
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug'      => 'test',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]);

		$page->version()->save(['headline' => 'Headline'], 'en');
		$page->version()->save(['headline' => ''], 'de');

		try {
			$this->validators($page)->validateContent();
			$this->fail('Expected an InvalidArgumentException');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('error.page.changeStatus.incomplete', $e->getKey());
			$this->assertSame(['headline'], array_keys($e->getDetails()));
			$this->assertSame('Headline (de)', $e->getDetails()['headline']['label']);
			$this->assertSame('de', $e->getDetails()['headline']['language']);
		}
	}

	public function testDoesNotExist(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateDoesNotExist());
	}

	public function testDoesNotExistWithExistingPage(): void
	{
		$page = new Page(['slug' => 'test']);

		Dir::make($page->root());

		$validators = $this->validators($page);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.draft.duplicate');

		$validators->validateDoesNotExist();
	}

	public function testDuplicate(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'a']]
			]
		]);

		$validators = $this->validators($this->app->page('a'));

		$this->assertNull($validators->validateDuplicate('a'));
	}

	public function testDuplicateWithExistingChild(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'a']]
			]
		]);

		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.duplicate');

		$validators->validateDuplicate('a');
	}

	public function testDuplicateWithExistingDraft(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [['slug' => 'a']]
			]
		]);

		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.draft.duplicate');

		$validators->validateDuplicate('a');
	}

	public function testDuplicateWithStrictMode(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'a']]
			]
		]);

		$validators = $this->validators($this->app->page('a'));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.duplicate');

		$validators->validateDuplicate('a', strict: true);
	}

	public function testEnsureChangeNum(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$validators->ensure('changeNum', -1);
	}

	public function testEnsureChangeStatus(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.status.invalid');

		$validators->ensure('changeStatus', 'does-not-exist');
	}

	public function testEnsureChangeStatusToListed(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$validators->ensure('changeStatusToListed', -1);
	}

	public function testEnsureChangeStatusToListedWithoutPosition(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->ensure('changeStatusToListed'));
	}

	public function testEnsureChangeTemplate(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTemplate.invalid');

		$validators->ensure('changeTemplate', 'does-not-exist');
	}

	public function testEnsureChangeTitle(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$validators->ensure('changeTitle', '');
	}

	public function testEnsureCreate(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->ensure('create'));
	}

	public function testEnsureCreateWithExistingDraft(): void
	{
		// the page itself does not exist yet, but a draft
		// with the same slug already does
		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [['slug' => 'test']]
			]
		]);

		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.draft.duplicate');

		$validators->ensure('create');
	}

	public function testEnsureCreateWithIncompleteDraft(): void
	{
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'isDraft'   => true,
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]));

		$this->assertNull($validators->ensure('create'));
	}

	public function testEnsureCreateWithIncompletePublishedPage(): void
	{
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'isDraft'   => false,
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$validators->ensure('create');
	}

	public function testEnsureDuplicate(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$validators->ensure('duplicate', '');
	}

	public function testEnsureMove(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'children' => [['slug' => 'child']]
					]
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.move.ancestor');

		$validators->ensure('move', $this->app->page('parent-a/child'));
	}

	public function testEnsurePublish(): void
	{
		// only drafts get their content validated
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'isDraft'   => false,
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]));

		$this->assertNull($validators->ensure('publish'));
	}

	public function testEnsurePublishWithIncompleteDraft(): void
	{
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'isDraft'   => true,
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$validators->ensure('publish');
	}

	public function testHasNoChildren(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateHasNoChildren());
	}

	public function testHasNoChildrenWithChildren(): void
	{
		$page = new Page([
			'slug'     => 'test',
			'children' => [['slug' => 'a']]
		]);

		$validators = $this->validators($page);

		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.delete.hasChildren');

		$validators->validateHasNoChildren();
	}

	public function testHasNoChildrenWithDrafts(): void
	{
		$page = new Page([
			'slug'   => 'test',
			'drafts' => [['slug' => 'a']]
		]);

		$validators = $this->validators($page);

		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.delete.hasChildren');

		$validators->validateHasNoChildren();
	}

	public function testMoveTo(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'subpages' => [
							'type'       => 'pagelist',
							'blueprints' => 'child'
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

		$validators = $this->validators($this->app->page('parent-a/child'));

		$this->assertNull(
			$validators->validateMoveTo($this->app->page('parent-b'))
		);
	}

	public function testMoveToTemplateWithDuplicateName(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'sections' => [
						'photos' => [
							'type'       => 'pages',
							'blueprints' => 'child'
						],
						'details' => [
							'type'   => 'fields',
							'fields' => [
								'photos' => ['type' => 'text']
							]
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'child'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		// the pages section claimed the name first, so it keeps its
		// page list field and the parent stays parentable
		$validators->validateMoveToTemplate($this->app->page('parent-b'));

		$this->assertTrue(true);
	}

	public function testMoveToTemplateWithExcept(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'subpages' => [
							'type'       => 'pagelist',
							'blueprints' => ['except' => ['article']]
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'album'],
					['slug' => 'parent-b', 'template' => 'parent'],
					['slug' => 'parent-c', 'template' => 'article']
				]
			]
		]);

		// any other blueprint is accepted
		$this->validators($this->app->page('parent-a'))->validateMoveToTemplate($this->app->page('parent-b'));

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.move.template');

		$this->validators($this->app->page('parent-c'))->validateMoveToTemplate($this->app->page('parent-b'));
	}

	public function testMoveToTemplateWithInvalidTemplate(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'subpages' => [
							'type'       => 'pagelist',
							'blueprints' => ['album']
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'article'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.move.template');

		$validators->validateMoveToTemplate($this->app->page('parent-b'));
	}

	public function testMoveToTemplateWithListOfOtherParent(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'subpages' => [
							'type'   => 'pagelist',
							'parent' => 'site'
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'article'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		// the only list shows the children of the site
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.move.noBlueprints');

		$validators->validateMoveToTemplate($this->app->page('parent-b'));
	}

	public function testMoveToTemplateWithNestedList(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'settings' => [
							'type'   => 'object',
							'fields' => [
								'subpages' => [
									'type'       => 'pagelist',
									'blueprints' => ['article']
								]
							]
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'article'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		// the list in the object field accepts the article
		$validators->validateMoveToTemplate($this->app->page('parent-b'));

		$this->assertTrue(true);
	}

	public function testMoveToTemplateWithoutPagesFields(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'info' => ['type' => 'info']
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'parent'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.move.noBlueprints');

		$validators->validateMoveToTemplate($this->app->page('parent-b'));
	}

	public function testMoveToTemplateWithUnrestrictedList(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/parent' => [
					'fields' => [
						'albums' => [
							'type'       => 'pagelist',
							'blueprints' => ['album']
						],
						'subpages' => [
							'type' => 'pagelist'
						]
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'parent-a', 'template' => 'article'],
					['slug' => 'parent-b', 'template' => 'parent']
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		// the list without restriction accepts the article
		$validators->validateMoveToTemplate($this->app->page('parent-b'));

		$this->assertTrue(true);
	}

	public function testMoveToWithAncestor(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'children' => [['slug' => 'child']]
					]
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a'));

		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.move.ancestor');

		$validators->validateMoveTo($this->app->page('parent-a/child'));
	}

	public function testMoveToWithDuplicate(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'children' => [['slug' => 'child']]
					],
					[
						'slug'     => 'parent-b',
						'children' => [['slug' => 'child']]
					]
				]
			]
		]);

		$validators = $this->validators($this->app->page('parent-a/child'));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.move.duplicate');

		$validators->validateMoveTo($this->app->page('parent-b'));
	}

	public function testNum(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateNum(2));
		$this->assertNull($validators->validateNum());
	}

	public function testNumWithNegativeNum(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$validators->validateNum(-1);
	}

	public function testSlug(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateSlug('test'));
	}

	public function testSlugLength(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateSlugLength('test'));
	}

	public function testSlugLengthWithEmptySlug(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$validators->validateSlugLength('');
	}

	public function testSlugLengthWithTooLongSlug(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.maxlength');

		$validators->validateSlugLength(str_repeat('a', 256));
	}

	public function testSlugProtectedPaths(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateSlugProtectedPaths('test'));
	}

	public function testSlugProtectedPathsWithPageParent(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));
		$parent     = new Page(['slug' => 'parent']);

		$this->assertNull(
			$validators->validateSlugProtectedPaths('panel', $parent)
		);
	}

	public function testSlugProtectedPathsWithReservedPath(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeSlug.reserved');

		$validators->validateSlugProtectedPaths('panel');
	}

	public function testSlugProtectedPathsWithReservedPathInSubfolder(): void
	{
		$this->app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com/sub'
			]
		]);

		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The path of top-level pages must not start with "panel"');

		$validators->validateSlugProtectedPaths('panel');
	}

	public function testSlugWithInvalidSlug(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$validators->validateSlug('');
	}

	public function testStatus(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateStatus('listed'));
	}

	public function testStatusWithInvalidStatus(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.status.invalid');

		$validators->validateStatus('invalid');
	}

	public function testTemplate(): void
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

		$validators = $this->validators($page);

		$this->assertNull($validators->validateTemplate('b'));
	}

	public function testTemplateWithInvalidTemplate(): void
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

		$validators = $this->validators($page);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTemplate.invalid');

		$validators->validateTemplate('c');
	}

	public function testTemplateWithSingleBlueprint(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTemplate.invalid');

		$validators->validateTemplate('default');
	}

	public function testTitle(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateTitle('Test'));
	}

	public function testTitleWithEmptyTitle(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$validators->validateTitle('');
	}

	public function testToBeCreatable(): void
	{
		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->assertNull($validators->validateToBeCreatable());
	}

	public function testToBeCreatableWithExistingDraft(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'drafts' => [['slug' => 'test']]
			]
		]);

		$validators = $this->validators(new Page(['slug' => 'test']));

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.draft.duplicate');

		$validators->validateToBeCreatable();
	}

	public function testToBeCreatableWithIncompletePublishedPage(): void
	{
		// the content is not validated before it has been added
		$validators = $this->validators(new Page([
			'slug'      => 'test',
			'isDraft'   => false,
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'headline' => [
						'type'     => 'text',
						'required' => true
					]
				]
			]
		]));

		$this->assertNull($validators->validateToBeCreatable());
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}

	protected function validators(Page $page): PageValidators
	{
		return new PageValidators(
			model: $page,
			user: $this->user()
		);
	}
}
