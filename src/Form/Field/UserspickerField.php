<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\UserPicker;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * Userspicker field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserspickerField extends ModelspickerField
{
	public function default(): array
	{
		if (
			$this->default === true &&
			$user = $this->kirby()->user()
		) {
			return [$this->toItem($user)];
		}

		return parent::default();
	}

	public function getIdFromItemArray(array $item): string|null
	{
		return $item['uuid'] ?? $item['id'] ?? $item['email'] ?? null;
	}

	public function picker(): UserPicker
	{
		return new UserPicker([
			'image'  => $this->image(),
			'info'   => $this->info(),
			'layout' => $this->layout(),
			'model'  => $this->model(),
			'page'   => $this->kirby()->api()->requestQuery('page'),
			'query'  => $this->query(),
			'search' => $this->kirby()->api()->requestQuery('search'),
			'text'   => $this->text()
		]);
	}

	/**
	 * @param \Kirby\Cms\User $user
	 */
	public function toItem(ModelWithContent $user): array
	{
		return (new UserItem(
			user:   $user,
			image:  $this->image(),
			info:   $this->info(),
			layout: $this->layout(),
			text:   $this->text()
		))->props();
	}

	public function toModel(string $id)
	{
		return $this->kirby()->user($id);
	}
}
