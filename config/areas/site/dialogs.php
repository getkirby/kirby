<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Cms\PageRules;
use Kirby\Cms\Url;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Controller\Dialog\ChangesDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeNameDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeSortDialogController;
use Kirby\Panel\Controller\Dialog\FileChangeTemplateDialogController;
use Kirby\Panel\Controller\Dialog\FileDeleteDialogController;
use Kirby\Panel\Controller\Dialog\SiteChangeTitleDialogController;
use Kirby\Panel\Field;
use Kirby\Panel\Ui\Dialogs\PageCreateDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuids;

$fields = require __DIR__ . '/../fields/dialogs.php';

return [
	'page.changeSort' => [
		'pattern' => 'pages/(:any)/changeSort',
		'load' => function (string $id) {
			$page = Find::page($id);

			if ($page->blueprint()->num() !== 'default') {
				throw new PermissionException(
					key: 'page.sort.permission',
					data: ['slug' => $page->slug()]
				);
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'position' => Field::pagePosition($page),
					],
					'submitButton' => I18n::translate('change'),
					'value' => [
						'position' => $page->panel()->position()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			Find::page($id)->changeStatus(
				'listed',
				$request->get('position')
			);

			return [
				'event' => 'page.sort',
			];
		}
	],

	'page.changeStatus' => [
		'pattern' => 'pages/(:any)/changeStatus',
		'load' => function (string $id) {
			$page      = Find::page($id);
			$blueprint = $page->blueprint();
			$status    = $page->status();
			$states    = [];
			$position  = null;

			foreach ($blueprint->status() as $key => $state) {
				$states[] = [
					'value' => $key,
					'text'  => $state['label'],
					'info'  => $state['text'],
				];
			}

			if ($status === 'draft') {
				$errors = $page->errors();

				// switch to the error dialog if there are
				// errors and the draft cannot be published
				if (count($errors) > 0) {
					return [
						'component' => 'k-error-dialog',
						'props'     => [
							'message' => I18n::translate('error.page.changeStatus.incomplete'),
							'details' => $errors,
						]
					];
				}
			}

			$fields = [
				'status' => [
					'label'    => I18n::translate('page.changeStatus.select'),
					'type'     => 'radio',
					'required' => true,
					'options'  => $states
				]
			];

			if ($blueprint->num() === 'default') {
				$fields['position'] = Field::pagePosition($page, [
					'when' => [
						'status' => 'listed'
					]
				]);

				$position = $page->panel()->position();
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields'       => $fields,
					'submitButton' => I18n::translate('change'),
					'value' => [
						'status'   => $status,
						'position' => $position
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			Find::page($id)->changeStatus(
				$request->get('status'),
				$request->get('position')
			);

			return [
				'event' => 'page.changeStatus',
			];
		}
	],

	'page.changeTemplate' => [
		'pattern' => 'pages/(:any)/changeTemplate',
		'load' => function (string $id) {
			$page       = Find::page($id);
			$blueprints = $page->blueprints();

			if (count($blueprints) <= 1) {
				throw new Exception(
					key: 'page.changeTemplate.invalid',
					data: ['slug' => $id]
				);
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'notice' => [
							'type'  => 'info',
							'theme' => 'notice',
							'text'  => I18n::translate('page.changeTemplate.notice')
						],
						'template' => Field::template($blueprints, [
							'required' => true
						])
					],
					'theme' => 'notice',
					'submitButton' => I18n::translate('change'),
					'value' => [
						'template' => $page->intendedTemplate()->name()
					]
				]
			];
		},
		'submit' => function (string $id) {
			$page     = Find::page($id);
			$template = App::instance()->request()->get('template');

			$page->changeTemplate($template);

			return [
				'event' => 'page.changeTemplate',
			];
		}
	],

	'page.changeTitle' => [
		'pattern' => 'pages/(:any)/changeTitle',
		'load' => function (string $id) {
			$kirby   = App::instance();
			$request = $kirby->request();

			$page        = Find::page($id);
			$permissions = $page->permissions();
			$select      = $request->get('select', 'title');

			// build the path prefix
			$path = match ($kirby->multilang()) {
				true  => Str::after($kirby->site()->url(), $kirby->url()) . '/',
				false => '/'
			};

			if ($parent = $page->parent()) {
				$path .= $parent->uri() . '/';
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'title' => Field::title([
							'required'  => true,
							'preselect' => $select === 'title',
							'disabled'  => $permissions->can('changeTitle') === false
						]),
						'slug' => Field::slug([
							'required'  => true,
							'preselect' => $select === 'slug',
							'path'      => $path,
							'disabled'  => $permissions->can('changeSlug') === false,
							'wizard'    => [
								'text'  => I18n::translate('page.changeSlug.fromTitle'),
								'field' => 'title'
							]
						])
					],
					'autofocus' => false,
					'submitButton' => I18n::translate('change'),
					'value' => [
						'title' => $page->title()->value(),
						'slug'  => $page->slug(),
					]
				]
			];
		},
		'submit' => function (string $id) {
			$page    = Find::page($id);
			$kirby   = $page->kirby();
			$request = $kirby->request();
			$title   = trim($request->get('title', ''));
			$slug    = trim($request->get('slug', ''));

			// basic input validation before we move on
			PageRules::validateTitleLength($title);
			PageRules::validateSlugLength($slug);

			// nothing changed
			if ($page->title()->value() === $title && $page->slug() === $slug) {
				return true;
			}

			// prepare the response
			$response = [
				'event' => []
			];

			// the page title changed
			if ($page->title()->value() !== $title) {
				$page = $page->changeTitle($title);
				$response['event'][] = 'page.changeTitle';
			}

			// the slug changed
			if ($page->slug() !== $slug) {
				$response['event'][] = 'page.changeSlug';

				$newPage = $page->changeSlug($slug);
				$oldUrl  = $page->panel()->url(true);
				$newUrl  = $newPage->panel()->url(true);

				// check for a necessary redirect after the slug has changed
				if (
					$kirby->panel()->referrer() === $oldUrl &&
					$oldUrl !== $newUrl
				) {
					$response['redirect'] = $newUrl;
				}
			}

			return $response;
		}
	],

	'page.create' => [
		'pattern' => 'pages/create',
		'load' => function () {
			$request = App::instance()->request();
			$dialog  = new PageCreateDialog(
				parentId: $request->get('parent'),
				sectionId: $request->get('section'),
				slug: $request->get('slug'),
				template: $request->get('template'),
				title: $request->get('title'),
				uuid: $request->get('uuid'),
				viewId: $request->get('view'),
			);

			return $dialog->load();
		},
		'submit' => function () {
			$request = App::instance()->request();
			$dialog  = new PageCreateDialog(
				parentId: $request->get('parent'),
				sectionId: $request->get('section'),
				slug: $request->get('slug'),
				template: $request->get('template'),
				title: $request->get('title'),
				uuid: $request->get('uuid'),
				viewId: $request->get('view'),
			);

			return $dialog->submit($request->get());
		}
	],

	'page.delete' => [
		'pattern' => 'pages/(:any)/delete',
		'load' => function (string $id) {
			$page = Find::page($id);
			$text = I18n::template('page.delete.confirm', [
				'title' => Escape::html($page->title()->value())
			]);

			if ($page->childrenAndDrafts()->count() > 0) {
				return [
					'component' => 'k-form-dialog',
					'props' => [
						'fields' => [
							'info' => [
								'type'  => 'info',
								'theme' => 'negative',
								'text'  => I18n::translate('page.delete.confirm.subpages')
							],
							'check' => [
								'label'   => I18n::translate('page.delete.confirm.title'),
								'type'    => 'text',
								'counter' => false
							]
						],
						'size'         => 'medium',
						'submitButton' => I18n::translate('delete'),
						'text'         => $text,
						'theme'        => 'negative',
					]
				];
			}

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => $text
				]
			];
		},
		'submit' => function (string $id) {
			$page     = Find::page($id);
			$kirby    = $page->kirby();
			$redirect = false;
			$request  = $kirby->request();
			$referrer = $kirby->panel()->referrer();
			$url      = $page->panel()->url(true);

			if (
				$page->childrenAndDrafts()->count() > 0 &&
				$request->get('check') !== $page->title()->value()
			) {
				throw new InvalidArgumentException(
					key: 'page.delete.confirm'
				);
			}

			$page->delete(true);

			// redirect to the parent model URL
			// if the dialog has been opened in the page view
			if ($referrer === $url) {
				$redirect = $page->parentModel()->panel()->url(true);
			}

			return [
				'event'    => 'page.delete',
				'redirect' => $redirect
			];
		}
	],

	'page.duplicate' => [
		'pattern' => 'pages/(:any)/duplicate',
		'load' => function (string $id) {
			$page            = Find::page($id);
			$hasChildren     = $page->hasChildren();
			$hasFiles        = $page->hasFiles();
			$toggleWidth     = '1/' . count(array_filter([$hasChildren, $hasFiles]));

			$fields = [
				'title' => Field::title([
					'required' => true
				]),
				'slug' => Field::slug([
					'required' => true,
					'path'     => $page->parent() ? '/' . $page->parent()->id() . '/' : '/',
					'wizard'   => [
						'text'  => I18n::translate('page.changeSlug.fromTitle'),
						'field' => 'title'
					]
				])
			];

			if ($hasFiles === true) {
				$fields['files'] = [
					'label' => I18n::translate('page.duplicate.files'),
					'type'  => 'toggle',
					'width' => $toggleWidth
				];
			}

			if ($hasChildren === true) {
				$fields['children'] = [
					'label' => I18n::translate('page.duplicate.pages'),
					'type'  => 'toggle',
					'width' => $toggleWidth
				];
			}

			$slugAppendix  = Url::slug(I18n::translate('page.duplicate.appendix'));
			$titleAppendix = I18n::translate('page.duplicate.appendix');

			// if the item to be duplicated already exists
			// add a suffix at the end of slug and title
			$duplicateSlug = $page->slug() . '-' . $slugAppendix;
			$siblingKeys   = $page->parentModel()->childrenAndDrafts()->pluck('uid');

			if (in_array($duplicateSlug, $siblingKeys, true) === true) {
				$suffixCounter = 2;
				$newSlug       = $duplicateSlug . $suffixCounter;

				while (in_array($newSlug, $siblingKeys, true) === true) {
					$newSlug = $duplicateSlug . ++$suffixCounter;
				}

				$slugAppendix  .= $suffixCounter;
				$titleAppendix .= ' ' . $suffixCounter;
			}

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields'       => $fields,
					'submitButton' => I18n::translate('duplicate'),
					'value' => [
						'children' => false,
						'files'    => false,
						'slug'     => $page->slug() . '-' . $slugAppendix,
						'title'    => $page->title() . ' ' . $titleAppendix
					]
				]
			];
		},
		'submit' => function (string $id) {
			$request = App::instance()->request();

			$newPage = Find::page($id)->duplicate($request->get('slug'), [
				'children' => (bool)$request->get('children'),
				'files'    => (bool)$request->get('files'),
				'title'    => (string)$request->get('title'),
			]);

			return [
				'event'    => 'page.duplicate',
				'redirect' => $newPage->panel()->url(true)
			];
		}
	],

	'page.fields' => [
		...$fields['model'],
		'pattern' => '(pages/.*?)/fields/(:any)/(:all?)',
	],
	'page.file.changeName' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeName',
		'action' => FileChangeNameDialogController::class
	],
	'page.file.changeSort' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeSort',
		'action' => FileChangeSortDialogController::class
	],
	'page.file.changeTemplate' => [
		'pattern' => '(pages/.*?)/files/(:any)/changeTemplate',
		'action' => FileChangeTemplateDialogController::class
	],
	'page.file.delete' => [
		'pattern' => '(pages/.*?)/files/(:any)/delete',
		'action' => FileDeleteDialogController::class
	],
	'page.file.fields' => [
		...$fields['file'],
		'pattern' => '(pages/.*?)/files/(:any)/fields/(:any)/(:all?)',
	],

	'page.move' => [
		'pattern' => 'pages/(:any)/move',
		'load'    => function (string $id) {
			$page   = Find::page($id);
			$parent = $page->parentModel();

			if (Uuids::enabled() === false) {
				$parentId = $parent?->id() ?? '/';
			} else {
				$parentId = $parent?->uuid()->toString() ?? 'site://';
			}

			return [
				'component' => 'k-page-move-dialog',
				'props' => [
					'value' => [
						'move'   => $page->panel()->url(true),
						'parent' => $parentId
					]
				]
			];
		},
		'submit' => function (string $id) {
			$kirby    = App::instance();
			$parentId = $kirby->request()->get('parent');
			$parent   = (empty($parentId) === true || $parentId === '/' || $parentId === 'site://') ? $kirby->site() : Find::page($parentId);
			$oldPage  = Find::page($id);
			$newPage  = $oldPage->move($parent);

			return [
				'event'    => 'page.move',
				'redirect' => $newPage->panel()->url(true)
			];
		}
	],

	'site.changeTitle' => [
		'pattern' => 'site/changeTitle',
		'action'  => SiteChangeTitleDialogController::class,
	],

	'site.fields' => [
		...$fields['model'],
		'pattern' => '(site)/fields/(:any)/(:all?)',
	],
	'site.file.changeName' => [
		'pattern' => '(site)/files/(:any)/changeName',
		'action' => FileChangeNameDialogController::class
	],
	'site.file.changeSort' => [
		'pattern' => '(site)/files/(:any)/changeSort',
		'action' => FileChangeSortDialogController::class
	],
	'site.file.changeTemplate' => [
		'pattern' => '(site)/files/(:any)/changeTemplate',
		'action' => FileChangeTemplateDialogController::class
	],
	'site.file.delete' => [
		'pattern' => '(site)/files/(:any)/delete',
		'action' => FileDeleteDialogController::class
	],
	'site.file.fields' => [
		...$fields['file'],
		'pattern' => '(site)/files/(:any)/fields/(:any)/(:all?)',
	],

	'changes' => [
		'pattern' => 'changes',
		'action'  => ChangesDialogController::class,
	],
];
