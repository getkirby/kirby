<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Http\Request;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class Drawer extends Component
{
	protected App $kirby;
	protected Request $request;

	public function __construct(
		string $component = 'k-drawer',
		string|null $class = null,
		public bool $disabled = false,
		public string|null $icon = null,
		public array|null $options = null,
		string|null $style = null,
		public string|null $title = null,
	) {
		parent::__construct(
			component: $component,
			class:     $class,
			style:     $style
		);

		$this->kirby   = App::instance();
		$this->request = $this->kirby->request();
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'disabled' => $this->disabled,
			'icon'     => $this->icon,
			'options'  => $this->options,
			'title'    => $this->title,
		];
	}
}
