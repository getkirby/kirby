<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Content\Translations;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

class NewPage extends Page
{
	use NewModelFixes;

	public static function create(array $props): Page
	{
		$props = self::normalizeProps($props);

		// create the instance without content or translations
		// to avoid that the page is created in memory storage
		$page = static::factory([
			...$props,
			'content'      => null,
			'translations' => null
		]);

		// merge the content with the defaults
		$props['content'] = [
			...$page->createDefaultContent(),
			...$props['content'],
		];

		// make sure that a UUID gets generated
		// and added to content right away
		if (Uuids::enabled() === true) {
			$props['content']['uuid'] ??= Uuid::generate();
		}

		// keep the initial storage class
		$storage = $page->storage()::class;

		// make sure that the temporary page is stored in memory
		$page->changeStorage(MemoryStorage::class);

		// inject the content
		$page->setContent($props['content']);

		// inject the translations
		$page->setTranslations($props['translations'] ?? null);

		// run the hooks and creation action
		$page = $page->commit(
			'create',
			[
				'page'  => $page,
				'input' => $props
			],
			function ($page) use ($storage) {
				// move to final storage
				return $page->changeStorage($storage);
			}
		);

		// publish the new page if a number is given
		if (isset($props['num']) === true) {
			$page = $page->changeStatus('listed', $props['num']);
		}

		return $page;
	}

	protected static function normalizeProps(array $props): array
	{
		$content  = $props['content']  ?? [];
		$template = $props['template'] ?? 'default';

		return [
			...$props,
			'content'  => $content,
			'isDraft'  => $props['isDraft'] ?? $props['draft'] ?? true,
			'model'    => $props['model']   ?? $template,
			'slug'     => Url::slug($props['slug'] ?? $content['title'] ?? null),
			'template' => $template,
		];
	}
}
