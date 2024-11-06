<?php

namespace Kirby\Cms;

/**
 * ModelPermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelPermissions extends NewPermissions
{
	protected ModelWithContent $model;
	protected array            $options;

	public function __construct(ModelWithContent $model)
	{
		$this->model       = $model;
		$this->options     = $model->blueprint()->options();
		$this->user        = $model->kirby()->user() ?? User::nobody();
		$this->permissions = $this->user->role()->permissions();
	}

	public function toArray(): array
	{
		$array = [];

		foreach ($this->options as $key => $value) {
			$array[$key] = $this->can($key);
		}

		return $array;
	}
}
