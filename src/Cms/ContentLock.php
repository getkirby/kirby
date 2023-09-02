<?php

namespace Kirby\Cms;

use Kirby\Exception\AuthException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;

/**
 * Takes care of content lock and unlock information
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ContentLock
{
	protected array $data;

	public function __construct(
		protected ModelWithContent $model
	) {
		$this->data = $this->kirby()->locks()->get($model);
	}

	/**
	 * Clears the lock unconditionally
	 */
	protected function clearLock(): bool
	{
		// if no lock exists, skip
		if (isset($this->data['lock']) === false) {
			return true;
		}

		// remove lock
		unset($this->data['lock']);

		return $this->kirby()->locks()->set($this->model, $this->data);
	}

	/**
	 * Sets lock with the current user
	 *
	 * @throws \Kirby\Exception\DuplicateException
	 */
	public function create(): bool
	{
		// check if model is already locked by another user
		if (
			isset($this->data['lock']) === true &&
			$this->data['lock']['user'] !== $this->user()->id()
		) {
			$id = ContentLocks::id($this->model);
			throw new DuplicateException($id . ' is already locked');
		}

		$this->data['lock'] = [
			'user' => $this->user()->id(),
			'time' => time()
		];

		return $this->kirby()->locks()->set($this->model, $this->data);
	}

	/**
	 * Returns either `false` or array  with `user`, `email`,
	 * `time` and `unlockable` keys
	 */
	public function get(): array|bool
	{
		$data = $this->data['lock'] ?? [];

		if (empty($data) === false && $data['user'] !== $this->user()->id()) {
			if ($user = $this->kirby()->user($data['user'])) {
				$time = (int)($data['time']);

				return [
					'user'       => $user->id(),
					'email'      => $user->email(),
					'time'       => $time,
					'unlockable' => ($time + 60) <= time()
				];
			}

			// clear lock if user not found
			$this->clearLock();
		}

		return false;
	}

	/**
	 * Returns if the model is locked by another user
	 */
	public function isLocked(): bool
	{
		$lock = $this->get();

		if ($lock !== false && $lock['user'] !== $this->user()->id()) {
			return true;
		}

		return false;
	}

	/**
	 * Returns if the current user's lock has been removed by another user
	 */
	public function isUnlocked(): bool
	{
		$data = $this->data['unlock'] ?? [];

		return in_array($this->user()->id(), $data) === true;
	}

	/**
	 * Returns the app instance
	 */
	protected function kirby(): App
	{
		return $this->model->kirby();
	}

	/**
	 * Removes lock of current user
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function remove(): bool
	{
		// if no lock exists, skip
		if (isset($this->data['lock']) === false) {
			return true;
		}

		// check if lock was set by another user
		if ($this->data['lock']['user'] !== $this->user()->id()) {
			throw new LogicException([
				'fallback' => 'The content lock can only be removed by the user who created it. Use unlock instead.',
				'httpCode' => 409
			]);
		}

		return $this->clearLock();
	}

	/**
	 * Removes unlock information for current user
	 */
	public function resolve(): bool
	{
		// if no unlocks exist, skip
		if (isset($this->data['unlock']) === false) {
			return true;
		}

		// remove user from unlock array
		$this->data['unlock'] = array_diff(
			$this->data['unlock'],
			[$this->user()->id()]
		);

		return $this->kirby()->locks()->set($this->model, $this->data);
	}

	/**
	 * Returns the state for the
	 * form buttons in the frontend
	 */
	public function state(): string|null
	{
		return match (true) {
			$this->isUnlocked() => 'unlock',
			$this->isLocked()   => 'lock',
			default => null
		};
	}

	/**
	 * Returns a usable lock array
	 * for the frontend
	 */
	public function toArray(): array
	{
		return [
			'state' => $this->state(),
			'data'  => $this->get()
		];
	}

	/**
	 * Removes current lock and adds lock user to unlock data
	 */
	public function unlock(): bool
	{
		// if no lock exists, skip
		if (isset($this->data['lock']) === false) {
			return true;
		}

		// add lock user to unlocked data
		$this->data['unlock'] ??= [];
		$this->data['unlock'][] = $this->data['lock']['user'];

		return $this->clearLock();
	}

	/**
	 * Returns currently authenticated user;
	 * throws exception if none is authenticated
	 *
	 * @throws \Kirby\Exception\PermissionException
	 */
	protected function user(): User
	{
		return $this->kirby()->user() ??
			throw new AuthException('No user authenticated.');
	}
}
