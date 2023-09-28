<?php

namespace Kirby\Cms;

use Kirby\Api\Api as BaseApi;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Form;
use Kirby\Session\Session;

/**
 * Api
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Api extends BaseApi
{
	protected App $kirby;

	public function __construct(array $props)
	{
		$this->kirby = $props['kirby'];
		parent::__construct($props);
	}

	/**
	 * Execute an API call for the given path,
	 * request method and optional request data
	 */
	public function call(
		string|null $path = null,
		string $method = 'GET',
		array $requestData = []
	): mixed {
		$this->setRequestMethod($method);
		$this->setRequestData($requestData);

		$this->kirby->setCurrentLanguage($this->language());

		$allowImpersonation = $this->kirby()->option('api.allowImpersonation', false);

		$translation   = $this->kirby->user(null, $allowImpersonation)?->language();
		$translation ??= $this->kirby->panelLanguage();
		$this->kirby->setCurrentTranslation($translation);

		return parent::call($path, $method, $requestData);
	}

	/**
	 * Creates a new instance while
	 * merging initial and new properties
	 */
	public function clone(array $props = []): static
	{
		return parent::clone(array_merge([
			'kirby' => $this->kirby
		], $props));
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException if the field type cannot be found or the field cannot be loaded
	 */
	public function fieldApi(
		ModelWithContent $model,
		string $name,
		string|null $path = null
	): mixed {
		$field = Form::for($model)->field($name);

		$fieldApi = $this->clone([
			'data'   => array_merge($this->data(), ['field' => $field]),
			'routes' => $field->api(),
		]);

		return $fieldApi->call(
			$path,
			$this->requestMethod(),
			$this->requestData()
		);
	}

	/**
	 * Returns the file object for the given
	 * parent path and filename
	 *
	 * @param string $path Path to file's parent model
	 * @throws \Kirby\Exception\NotFoundException if the file cannot be found
	 */
	public function file(
		string $path,
		string $filename
	): File|null {
		return Find::file($path, $filename);
	}

	/**
	 * Returns the all readable files for the parent
	 *
	 * @param string $path Path to file's parent model
	 * @throws \Kirby\Exception\NotFoundException if the file cannot be found
	 */
	public function files(string $path): Files
	{
		return $this->parent($path)->files()->filter('isAccessible', true);
	}

	/**
	 * Returns the model's object for the given path
	 *
	 * @param string $path Path to parent model
	 * @throws \Kirby\Exception\InvalidArgumentException if the model type is invalid
	 * @throws \Kirby\Exception\NotFoundException if the model cannot be found
	 */
	public function parent(string $path): ModelWithContent|null
	{
		return Find::parent($path);
	}

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->kirby;
	}

	/**
	 * Returns the language request header
	 */
	public function language(): string|null
	{
		return
			$this->requestQuery('language') ??
			$this->requestHeaders('x-language');
	}

	/**
	 * Returns the page object for the given id
	 *
	 * @param string $id Page's id
	 * @throws \Kirby\Exception\NotFoundException if the page cannot be found
	 */
	public function page(string $id): Page|null
	{
		return Find::page($id);
	}

	/**
	 * Returns the subpages for the given
	 * parent. The subpages can be filtered
	 * by status (draft, listed, unlisted, published, all)
	 */
	public function pages(
		string|null $parentId = null,
		string|null $status = null
	): Pages {
		$parent = $parentId === null ? $this->site() : $this->page($parentId);
		$pages  = match ($status) {
			'all'             => $parent->childrenAndDrafts(),
			'draft', 'drafts' => $parent->drafts(),
			'listed'          => $parent->children()->listed(),
			'unlisted'        => $parent->children()->unlisted(),
			'published'       => $parent->children(),
			default           => $parent->children()
		};

		return $pages->filter('isAccessible', true);
	}

	/**
	 * Search for direct subpages of the
	 * given parent
	 */
	public function searchPages(string|null $parent = null): Pages
	{
		$pages = $this->pages($parent, $this->requestQuery('status'));

		if ($this->requestMethod() === 'GET') {
			return $pages->search($this->requestQuery('q'));
		}

		return $pages->query($this->requestBody());
	}

	/**
	 * Returns the current Session instance
	 *
	 * @param array $options Additional options, see the session component
	 */
	public function session(array $options = []): Session
	{
		return $this->kirby->session(array_merge([
			'detect' => true
		], $options));
	}

	/**
	 * Returns the site object
	 */
	public function site(): Site
	{
		return $this->kirby->site();
	}

	/**
	 * Returns the user object for the given id or
	 * returns the current authenticated user if no
	 * id is passed
	 *
	 * @param string|null $id User's id
	 * @throws \Kirby\Exception\NotFoundException if the user for the given id cannot be found
	 */
	public function user(string|null $id = null): User|null
	{
		try {
			return Find::user($id);
		} catch (NotFoundException $e) {
			if ($id === null) {
				return null;
			}

			throw $e;
		}
	}

	/**
	 * Returns the users collection
	 */
	public function users(): Users
	{
		return $this->kirby->users();
	}
}
