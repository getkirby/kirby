<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Toolkit\Str;

class Lock
{
	protected bool $isActive = false;
	protected int $modified = 0;
	protected User|null $user = null;

	public function __construct(
		protected ModelWithContent $model,
		protected User|null $authenticated = null
	) {
		// set the currently authenticated user as default if no user is given
		$this->authenticated ??= $this->model->kirby()->user();

		// get the recent changes for the model
		$changes = $this->model->version(VersionId::changes());

		// the model is not locked if no changes exist
		if ($changes->exists() === false) {
			return;
		}

		// get the editing user
		if ($userId = ($changes->read()['lock'] ?? null)) {
			$this->user = App::instance()->user($userId);
		}

		// get last modification time
		$this->modified = $changes->modified();

		// the model is not locked if no user could be found
		if ($this->user === null) {
			return;
		}

		// the model is not locked if the editing user is the currently logged in user
		if ($this->user->is($this->authenticated) === true) {
			return;
		}

		$this->isActive = true;
	}

	public function isActive(): bool
	{
		return $this->isActive;
	}

	public function modified(
		string|null $format = null,
		string|null $handler = null
	): int|string|false|null {
		return Str::date($this->modified, $format, $handler);
	}

	public function toArray(): array
	{
		return [
			'isActive' => $this->isActive,
			'modified' => $this->modified,
			'user'     => [
				'id'    => $this->user?->id(),
				'email' => $this->user?->email()
			]
		];
	}

	public function user(): User|null
	{
		return $this->user;
	}
}
