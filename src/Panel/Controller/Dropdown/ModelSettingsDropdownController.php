<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Controller\DropdownController;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class ModelSettingsDropdownController extends DropdownController
{
	protected array $context = [];
	protected ModelWithContent $model;
	protected array $permissions;

	/**
	 * Checks for disabled options according
	 * to the given permissions
	 */
	public function isDisabledOption(string $action): bool
	{
		$permission = $this->permissions[$action] ?? false;
		$context    = $this->context[$action] ?? true;

		return
			$permission === false ||
			$context === false ||
			$context === 'false';
	}

	public function model(): ModelWithContent
	{
		return $this->model;
	}

	protected function view(): string
	{
		return $this->context['view'] ?? 'view';
	}
}
