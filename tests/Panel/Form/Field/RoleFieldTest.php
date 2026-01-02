<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\Roles;
use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RoleField::class)]
class RoleFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = new RoleField();
		$props = $field->props();

		ksort($props);

		// test options separately
		unset($props['options']);

		$expected = [
			'autofocus'   => false,
			'columns'     => 1,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'label'       => 'Role',
			'name'        => 'role',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'radio',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
		$this->assertCount($this->app->roles()->count(), $field->options());
	}

	public function testOptions(): void
	{
		$roles = Roles::factory([
			[
				'name'        => 'admin',
				'title'       => 'Admin',
				'description' => 'Admin description'
			],
			[
				'name'        => 'editor',
				'title'       => 'Editor',
				'description' => 'Editor description'
			],
			[
				'name'  => 'client',
				'title' => 'Client'
			]
		]);

		$field = new RoleField(roles: $roles);

		$expected = [
			[
				'text'  => 'Admin',
				'info'  => 'Admin description',
				'value' => 'admin'
			],
			[
				'text'  => 'Client',
				'info'  => 'No description',
				'value' => 'client'
			],
			[
				'text'  => 'Editor',
				'info'  => 'Editor description',
				'value' => 'editor'
			]
		];

		$this->assertSame($expected, $field->options());
	}

	public function testLabel(): void
	{
		$field = new RoleField(
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}
}
