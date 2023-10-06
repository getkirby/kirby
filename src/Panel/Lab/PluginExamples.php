<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;

class PluginExamples extends Examples
{
	public function __construct()
	{
		$this->id	 = 'site';
		$this->root  = App::instance()->root('site') . '/lab';
		$this->props = [
			'name' => 'Your examples',
			'icon' => 'live'
		];
	}
}

