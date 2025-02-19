<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

class NewPage extends Page
{
	use NewModelFixes;

	public static function create(array $props): Page
	{
		$content  = $props['content'] ?? [];
		$template = $props['template'] ?? 'default';
		$model    = $props['model'] ?? $template;

		// create the instance with a limited set of props
		$page = static::factory($props = [
			...$props,
			'content'      => null,
			'isDraft'      => $props['isDraft'] ?? $props['draft'] ?? true,
			'model'        => $model,
			'slug'         => Url::slug($props['slug'] ?? $content['title'] ?? null),
			'template'     => $template,
			'translations' => null,
		]);

		// create the form to get the generate the defaults
		$form = Form::for($page, [
			'language' => Language::ensure('default')->code(),
		]);

		// merge the content back with the defaults
		$props['content'] = [
			...$form->strings(true),
			...$content,
		];

		// add a uuid if not already set
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] ??= Uuid::generate();
		}

		// keep the initial storage class
		$storage = get_class($page->storage());

		// keep the page in memory until it will be saved
		$page->moveToStorage(new MemoryStorage($page));

		// inject the content to make this page object usable in the hook
		$page = $page->save($props['content'], 'default');

		// run the hooks and creation action
		$page = $page->commit(
			'create',
			[
				'page'  => $page,
				'input' => $props
			],
			function ($page, $props) use ($storage) {
				// move to final storage
				$page->moveToStorage(new $storage($page));

				// flush the parent cache to get children and drafts right
				static::updateParentCollections($page, 'append');

				return $page;
			}
		);

		// publish the new page if a number is given
		if (isset($props['num']) === true) {
			$page = $page->changeStatus('listed', $props['num']);
		}

		return $page;
	}

	protected function setTemplate(string|null $template = null): static
	{
		if ($template !== null) {
			$this->intendedTemplate = $this->kirby()->template(strtolower($template));
		}

		return $this;
	}
}
