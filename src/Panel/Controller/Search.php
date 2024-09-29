<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;

/**
 * The Search controller takes care of the logic
 * for delivering Panel search results
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @internal
 */
class Search
{
	public static function files(
		string|null $query = null,
		int|null $limit = null,
		int $page = 1
	): array {
		$kirby = App::instance();
		$files = $kirby->site()
			->index(true)
			->filter('isListable', true)
			->files();

		// add site files which aren't considered by the index
		$files = $files->add($kirby->site()->files());

		// filter and search among those files
		$files = $files->filter('isListable', true)->search($query);

		if ($limit !== null) {
			$files = $files->paginate($limit, $page);
		}

		return [
			'results' => $files->values(fn ($file) => [
				'image' => $file->panel()->image(),
				'text'  => Escape::html($file->filename()),
				'link'  => $file->panel()->url(true),
				'info'  => Escape::html($file->id()),
				'uuid'  => $file->uuid()->toString(),
			]),
			'pagination' => $files->pagination()?->toArray()
		];
	}

	public static function pages(
		string|null $query = null,
		int|null $limit = null,
		int $page = 1
	): array {
		$kirby = App::instance();
		$pages = $kirby->site()
			->index(true)
			->search($query)
			->filter('isListable', true);

		if ($limit !== null) {
			$pages = $pages->paginate($limit, $page);
		}

		return [
			'results' => $pages->values(fn ($page) => [
				'image' => $page->panel()->image(),
				'text' => Escape::html($page->title()->value()),
				'link' => $page->panel()->url(true),
				'info' => Escape::html($page->id()),
				'uuid' => $page->uuid()?->toString(),
			]),
			'pagination' => $pages->pagination()?->toArray()
		];
	}

	public static function users(
		string|null $query = null,
		int|null $limit = null,
		int $page = 1
	): array {
		$kirby = App::instance();
		$users = $kirby->users()->search($query);

		if ($limit !== null) {
			$users = $users->paginate($limit, $page);
		}

		return [
			'results' => $users->values(fn ($user) => [
				'image' => $user->panel()->image(),
				'text'  => Escape::html($user->username()),
				'link'  => $user->panel()->url(true),
				'info'  => Escape::html($user->role()->title()),
				'uuid'  => $user->uuid()->toString(),
			]),
			'pagination' => $users->pagination()?->toArray()
		];
	}
}
