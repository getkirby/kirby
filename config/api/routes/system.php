<?php

use Kirby\Panel\Controller\View\InstallationViewController;

/**
 * System Routes
 */
return [

	[
		'pattern' => 'system',
		'method'  => 'GET',
		'auth'    => false,
		'action'  => function () {
			$system = $this->kirby()->system();

			if ($this->kirby()->user()) {
				return $system;
			}

			$info = match ($system->isOk()) {
				true  => $this->resolve($system)->view('login')->toArray(),
				false => $this->resolve($system)->view('troubleshooting')->toArray()
			};

			return [
				'status' => 'ok',
				'data'   => $info,
				'type'   => 'model'
			];
		}
	],
	[
		'pattern' => 'system/register',
		'method'  => 'POST',
		'action'  => function () {
			return $this->kirby()->system()->register($this->requestBody('license'), $this->requestBody('email'));
		}
	],
	[
		'pattern' => 'system/install',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			$controller = new InstallationViewController();
			$controller->submit();

			$user = $this->users()->first();

			return [
				'status' => 'ok',
				'token'  => true,
				'user'   => $this->resolve($user)->view('auth')->toArray()
			];
		}
	]

];
