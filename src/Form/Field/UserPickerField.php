<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Panel\Controller\Dialog\UserPickerDialogController;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * Userpicker field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserPickerField extends ModelPickerField
{
	public function default(): array
	{
		if (
			$this->default === true &&
			$user = $this->kirby()->user()
		) {
			return [$user->id()];
		}

		return parent::default();
	}

	public function dialogs(): array
	{
		return [
			'picker' => fn () => new UserPickerDialogController(...[
				'model'     => $this->model(),
				'hasSearch' => $this->search(),
				'image'     => $this->image(),
				'info'      => $this->info(),
				'max'       => $this->max(),
				'multiple'  => $this->multiple(),
				'query'     => $this->query(),
				'text'      => $this->text(),
				...$this->picker()
			])
		];
	}

	/**
	 * @param \Kirby\Cms\User $model
	 */
	public function toItem(ModelWithContent $model): array
	{
		return (new UserItem(
			user: $model,
			image: $this->image(),
			info: $this->info(),
			layout: $this->layout(),
			text: $this->text()
		))->props();
	}

	public function toModel(string $id): User|null
	{
		return $this->kirby()->user($id);
	}
}
