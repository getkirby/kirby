<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\Exception;

/**
 * The ModelCommit class is used to commit a given model action
 * in the model action classes. It takes care of running
 * the `before` and `after` hooks and updating the state
 * of the given model.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ModelCommit
{
	protected App $kirby;
	protected string $prefix;

	public function __construct(
		protected ModelWithContent $model,
		protected string $action
	) {
		$this->kirby  = $this->model->kirby();
		$this->prefix = $this->model::CLASS_ALIAS;
	}

	/**
	 * Runs the `after` hook and returns the result.
	 */
	public function after(mixed $state): mixed
	{
		// run the `after` hook
		$arguments = $this->afterHookArguments($state);
		$hook      = $this->hook('after', $arguments);

		// flush the page cache after any model action
		$this->kirby->cache('pages')->flush();

		return $hook['result'];
	}

	/**
	 * Returns the appropriate arguments for the `after` hook
	 * for the given model action. It's a wrapper around the
	 * more specific `afterHookArgumentsFor*Actions` methods.
	 */
	public function afterHookArguments(mixed $state): array
	{
		return match (true) {
			$this->model instanceof File =>
				$this->afterHookArgumentsForFileActions($this->model, $this->action, $state),
			$this->model instanceof Page =>
				$this->afterHookArgumentsForPageActions($this->model, $this->action, $state),
			$this->model instanceof Site =>
				$this->afterHookArgumentsForSiteActions($this->model, $state),
			$this->model instanceof User =>
				$this->afterHookArgumentsForUserActions($this->model, $this->action, $state),
			default =>
				throw new Exception('Invalid model class')
		};
	}

	/**
	 * Returns the appropriate arguments for the `after` hook
	 * for the given page action.
	 */
	protected function afterHookArgumentsForPageActions(
		Page $model,
		string $action,
		mixed $state
	): array {
		return match ($action) {
			'create' => [
				'page' => $state
			],
			'duplicate' => [
				'duplicatePage' => $state,
				'originalPage'  => $model
			],
			'delete' => [
				'status' => $state,
				'page'   => $model
			],
			default => [
				'newPage' => $state,
				'oldPage' => $model
			]
		};
	}

	/**
	 * Returns the appropriate arguments for the `after` hook
	 * for the given file action.
	 */
	protected function afterHookArgumentsForFileActions(
		File $model,
		string $action,
		mixed $state
	): array {
		return match ($action) {
			'create' => [
				'file' => $state
			],
			'delete' => [
				'status' => $state,
				'file'   => $model
			],
			default  => [
				'newFile' => $state,
				'oldFile' => $model
			]
		};
	}

	/**
	 * Returns the appropriate arguments for the `after` hook
	 * for the given site action.
	 */
	protected function afterHookArgumentsForSiteActions(
		Site $model,
		mixed $state
	): array {
		return [
			'newSite' => $state,
			'oldSite' => $model
		];
	}

	/**
	 * Returns the appropriate arguments for the `after` hook
	 * for the given user action.
	 */
	protected function afterHookArgumentsForUserActions(
		User $model,
		string $action,
		mixed $state
	): array {
		return match ($action) {
			'create' =>	[
				'user' => $state
			],
			'delete' => [
				'status' => $state,
				'user'   => $model
			],
			default  => [
				'newUser' => $state,
				'oldUser' => $model
			]
		};
	}

	/**
	 * Runs the `before` hook and modifies the arguments
	 */
	public function before(array $arguments): array
	{
		// check model rules
		$this->validate($arguments);

		// run the `before` hook
		$hook = $this->hook('before', $arguments);

		// check model rules again, after the hook got applied
		$this->validate($hook['arguments']);

		return $hook['arguments'];
	}

	/**
	 * Handles the full call of the given action,
	 * runs the `before` and `after` hooks and updates
	 * the state of the given model.
	 */
	public function call(array $arguments, Closure $callback): mixed
	{
		// run the before hook
		$arguments = $this->before($arguments);

		// run the commit action
		$state = $callback(...array_values($arguments));

		// update the state for the after hook
		ModelState::update(
			method: $this->action,
			current: $this->model,
			next: $state
		);

		// run the after hook and return the result
		return $this->after($state);
	}

	/**
	 * Runs the given hook and modifies the first argument
	 * of the given arguments array. It returns an array with
	 * `arguments` and `result` keys.
	 */
	public function hook(string $hook, array $arguments): array
	{
		// the very first argument (which should be the model)
		// is modified by the return value from the hook (if any returned)
		$appliedTo = array_key_first($arguments);

		// run the hook and modify the first argument
		$arguments[$appliedTo] = $this->kirby->apply(
			// e.g. page.create:before
			$this->prefix . '.' . $this->action . ':' . $hook,
			$arguments,
			$appliedTo
		);

		return [
			'arguments' => $arguments,
			'result'    => $arguments[$appliedTo],
		];
	}

	/**
	 * Checks the model rules for the given action
	 * if there's a matching rule method.
	 */
	public function validate(array $arguments): void
	{
		$rules = match (true) {
			$this->model instanceof File => FileRules::class,
			$this->model instanceof Page => PageRules::class,
			$this->model instanceof Site => SiteRules::class,
			$this->model instanceof User => UserRules::class,
			default => throw new Exception('Invalid model class') // @codeCoverageIgnore
		};

		if (method_exists($rules, $this->action) === true) {
			$rules::{$this->action}(...array_values($arguments));
		}
	}
}
