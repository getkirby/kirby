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
	/**
	 * @var App
	 */
	protected $kirby;

	/**
	 * Execute an API call for the given path,
	 * request method and optional request data
	 */
	public function call(
		string|null $path = null,
		string $method = 'GET',
		array $requestData = []
	) {
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
	 * @throws \Kirby\Exception\NotFoundException if the field type cannot be found or the field cannot be loaded
	 */
	public function fieldApi($model, string $name, string|null $path = null)
	{
		$field = Form::for($model)->field($name);

		$fieldApi = new static(
			array_merge($this->propertyData, [
				'data'   => array_merge($this->data(), ['field' => $field]),
				'routes' => $field->api(),
			]),
		);

		return $fieldApi->call($path, $this->requestMethod(), $this->requestData());
	}

	/**
	 * Returns the file object for the given
	 * parent path and filename
	 *
	 * @param string|null $path Path to file's parent model
	 * @throws \Kirby\Exception\NotFoundException if the file cannot be found
	 */
	public function file(string|null $path = null, string $filename): File|null
	{
		return Find::file($path, $filename);
	}

	/**
	 * Returns the model's object for the given path
	 *
	 * @param string $path Path to parent model
	 * @throws \Kirby\Exception\InvalidArgumentException if the model type is invalid
	 * @throws \Kirby\Exception\NotFoundException if the model cannot be found
	 */
	public function parent(string $path): Model|null
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
		return $this->requestQuery('language') ?? $this->requestHeaders('x-language');
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
	public function pages(string|null $parentId = null, string|null $status = null): Pages
	{
		$parent = $parentId === null ? $this->site() : $this->page($parentId);

		return match ($status) {
			'all'             => $parent->childrenAndDrafts(),
			'draft', 'drafts' => $parent->drafts(),
			'listed'          => $parent->children()->listed(),
			'unlisted'        => $parent->children()->unlisted(),
			'published'       => $parent->children(),
			default           => $parent->children()
		};
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
	 * Setter for the parent Kirby instance
	 *
	 * @return $this
	 */
	protected function setKirby(App $kirby): static
	{
		$this->kirby = $kirby;
		return $this;
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
