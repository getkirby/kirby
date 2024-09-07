<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Field
 */
class FieldTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Field';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::email
	 */
	public function testEmail(): void
	{
		// default
		$field = Field::email();
		$expected = [
			'label'   => 'Email',
			'type'    => 'email',
			'counter' => false
		];

		$this->assertSame($expected, $field);

		// with custom props
		$field = Field::email([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::filePosition
	 */
	public function testFilePosition(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				]
			]
		]);

		$site = $this->app->site();
		$file = $site->file('b.jpg');

		// default
		$field = Field::filePosition($file);

		$this->assertSame('Change position', $field['label']);
		$this->assertSame('select', $field['type']);
		$this->assertFalse($field['empty']);

		// check options
		$this->assertCount(5, $field['options']);

		$this->assertSame(1, $field['options'][0]['value']);
		$this->assertSame(1, $field['options'][0]['text']);

		$this->assertSame('a.jpg', $field['options'][1]['value']);
		$this->assertSame('a.jpg', $field['options'][1]['text']);
		$this->assertTrue($field['options'][1]['disabled']);

		$this->assertSame(2, $field['options'][2]['value']);
		$this->assertSame(2, $field['options'][2]['text']);

		$this->assertSame('c.jpg', $field['options'][3]['value']);
		$this->assertSame('c.jpg', $field['options'][3]['text']);
		$this->assertTrue($field['options'][3]['disabled']);

		$this->assertSame(3, $field['options'][4]['value']);
		$this->assertSame(3, $field['options'][4]['text']);

		// with custom props
		$field = Field::filePosition($file, [
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::hidden
	 */
	public function testHidden(): void
	{
		$field = Field::hidden();
		$this->assertSame(['hidden' => true], $field);
	}

	/**
	 * @covers ::pagePosition
	 */
	public function testPagePosition(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
					['slug' => 'b', 'num' => 2],
					['slug' => 'c', 'num' => 3]
				]
			]
		]);

		$site = $this->app->site();
		$page = $site->find('b');

		// default
		$field = Field::pagePosition($page);

		$this->assertSame('Please select a position', $field['label']);
		$this->assertSame('select', $field['type']);
		$this->assertFalse($field['empty']);

		// check options
		$this->assertCount(5, $field['options']);

		$this->assertSame(1, $field['options'][0]['value']);
		$this->assertSame(1, $field['options'][0]['text']);

		$this->assertSame('a', $field['options'][1]['value']);
		$this->assertSame('a', $field['options'][1]['text']);
		$this->assertTrue($field['options'][1]['disabled']);

		$this->assertSame(2, $field['options'][2]['value']);
		$this->assertSame(2, $field['options'][2]['text']);

		$this->assertSame('c', $field['options'][3]['value']);
		$this->assertSame('c', $field['options'][3]['text']);
		$this->assertTrue($field['options'][3]['disabled']);

		$this->assertSame(3, $field['options'][4]['value']);
		$this->assertSame(3, $field['options'][4]['text']);

		// with custom props
		$field = Field::pagePosition($page, [
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::pagePosition
	 */
	public function testPagePositionWithNotEnoughOptions(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
				]
			]
		]);

		$site  = $this->app->site();
		$page  = $site->find('a');
		$field = Field::pagePosition($page);

		$this->assertTrue($field['hidden']);
	}

	/**
	 * @covers ::password
	 */
	public function testPassword(): void
	{
		// default
		$field = Field::password();
		$expected = [
			'label'   => 'Password',
			'type'    => 'password',
		];

		$this->assertSame($expected, $field);

		// with custom props
		$field = Field::password([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::role
	 */
	public function testRole(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);


		// pass roles
		$this->app->impersonate('admin@getkirby.com');
		$user  = $this->app->user('editor@getkirby.com');
		$field = Field::role(roles: $user->roles());
		$expected = [
			'label'   => 'Role',
			'type'    => 'radio',
			'value'   => null,
			'options' => [
				[
					'text'  => 'Admin',
					'info'  => 'No description',
					'value' => 'admin'
				],
				[
					'text'  => 'Editor',
					'info'  => 'No description',
					'value' => 'editor'
				],
				[
					'text'  => 'Guest',
					'info'  => 'No description',
					'value' => 'guest'
				]
			]
		];

		$this->assertSame($expected, $field);

		// pass no roles
		$field = Field::role();
		$expected = [
			'label'   => 'Role',
			'type'    => 'radio',
			'value'   => null,
			'options' => [
				[
					'text'  => 'Admin',
					'info'  => 'No description',
					'value' => 'admin'
				],
				[
					'text'  => 'Editor',
					'info'  => 'No description',
					'value' => 'editor'
				],
				[
					'text'  => 'Guest',
					'info'  => 'No description',
					'value' => 'guest'
				]
			]
		];

		$this->assertSame($expected, $field);

		// pass no roles, but current user is not an admin
		$this->app->impersonate('editor@getkirby.com');
		$field = Field::role();
		$expected = [
			'label'   => 'Role',
			'type'    => 'radio',
			'value'   => null,
			'options' => [
				[
					'text'  => 'Editor',
					'info'  => 'No description',
					'value' => 'editor'
				],
				[
					'text'  => 'Guest',
					'info'  => 'No description',
					'value' => 'guest'
				]
			]
		];

		$this->assertSame($expected, $field);

		// last admin
		$user  = $this->app->user('admin@getkirby.com');
		$field = Field::role(roles: $user->roles());
		$expected = [
			'label'   => 'Role',
			'type'    => 'radio',
			'value'   => 'admin',
			'options' => [
				[
					'text'  => 'Admin',
					'info'  => 'No description',
					'value' => 'admin'
				]
			]
		];

		$this->assertSame($expected, $field);
	}

	/**
	 * @covers ::slug
	 */
	public function testSlug(): void
	{
		// default
		$field = Field::slug();
		$expected = [
			'label' => 'URL appendix',
			'type'  => 'slug',
			'allow' => 'a-z0-9'
		];

		$this->assertSame($expected, $field);

		// with custom props
		$field = Field::slug([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::title
	 */
	public function testTitle(): void
	{
		// default
		$field = Field::title();
		$expected = [
			'label' => 'Title',
			'type'  => 'text',
			'icon'  => 'title'
		];

		$this->assertSame($expected, $field);

		// with custom props
		$field = Field::title([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::template
	 */
	public function testTemplate(): void
	{
		// default = no templates available
		$field    = Field::template();
		$expected = [
			'label'    => 'Template',
			'type'     => 'select',
			'empty'    => false,
			'options'  => [],
			'icon'     => 'template',
			'disabled' => true
		];

		$this->assertSame($expected, $field);

		// select option format
		$options = [
			[
				'text'  => 'A',
				'value' => 'a'
			],
			[
				'text'  => 'B',
				'value' => 'b'
			]
		];

		$field = Field::template($options);
		$this->assertSame($options, $field['options']);
		$this->assertFalse($field['disabled']);

		// blueprint format
		$blueprints = [
			[
				'title' => 'A',
				'name'  => 'a'
			],
			[
				'title' => 'B',
				'name'  => 'b'
			]
		];

		$expected = [
			[
				'text'  => 'A',
				'value' => 'a'
			],
			[
				'text'  => 'B',
				'value' => 'b'
			]
		];

		$field = Field::template($blueprints);
		$this->assertSame($expected, $field['options']);
		$this->assertFalse($field['disabled']);

		// with custom props
		$field = Field::template([], ['required' => true]);
		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::translation
	 */
	public function testTranslation(): void
	{
		// default
		$field = Field::translation();

		$this->assertSame('Language', $field['label']);
		$this->assertSame('select', $field['type']);
		$this->assertSame('translate', $field['icon']);
		$this->assertFalse($field['empty']);
		$this->assertCount($this->app->translations()->count(), $field['options']);

		// with custom props
		$field = Field::translation([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}

	/**
	 * @covers ::username
	 */
	public function testUsername(): void
	{
		// default
		$field = Field::username();
		$expected = [
			'icon'  => 'user',
			'label' => 'Name',
			'type'  => 'text',
		];

		$this->assertSame($expected, $field);

		// with custom props
		$field = Field::username([
			'required' => true
		]);

		$this->assertTrue($field['required']);
	}
}
