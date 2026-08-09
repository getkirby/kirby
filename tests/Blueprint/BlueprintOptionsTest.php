<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blueprint::class)]
class BlueprintOptionsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.BlueprintOptions';

	protected ModelWithContent $model;

	protected function setUp(): void
	{
		$this->app = new App([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->model = new Page(['slug' => 'test']);
	}

	protected function blueprint(array $options): Blueprint
	{
		return new Blueprint([
			'model'   => $this->model,
			'options' => $options
		]);
	}

	protected function user(string $role): User
	{
		return $this->app->user($role . '@getkirby.com');
	}

	public function testOptionForUserWithoutOptions(): void
	{
		$blueprint = new Blueprint(['model' => $this->model]);

		$this->assertNull($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithUndefinedOption(): void
	{
		$blueprint = $this->blueprint(['update' => true]);

		$this->assertNull($blueprint->optionForUser($this->user('editor'), 'delete'));
	}

	public function testOptionForUserWithTrue(): void
	{
		$blueprint = $this->blueprint(['update' => true]);

		$this->assertTrue($blueprint->optionForUser($this->user('admin'), 'update'));
		$this->assertTrue($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithFalse(): void
	{
		$blueprint = $this->blueprint(['update' => false]);

		$this->assertFalse($blueprint->optionForUser($this->user('admin'), 'update'));
		$this->assertFalse($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithRoles(): void
	{
		$blueprint = $this->blueprint([
			'update' => [
				'admin'  => true,
				'editor' => false
			]
		]);

		$this->assertTrue($blueprint->optionForUser($this->user('admin'), 'update'));
		$this->assertFalse($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithWildcard(): void
	{
		$blueprint = $this->blueprint([
			'update' => [
				'admin' => true,
				'*'     => false
			]
		]);

		$this->assertTrue($blueprint->optionForUser($this->user('admin'), 'update'));
		$this->assertFalse($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithMissingRole(): void
	{
		$blueprint = $this->blueprint([
			'update' => [
				'admin' => true
			]
		]);

		$this->assertTrue($blueprint->optionForUser($this->user('admin'), 'update'));
		$this->assertNull($blueprint->optionForUser($this->user('editor'), 'update'));
	}

	public function testOptionForUserWithList(): void
	{
		// a non-associative array is not a set of role rules
		$blueprint = $this->blueprint([
			'changeTemplate' => ['article', 'note']
		]);

		$this->assertNull(
			$blueprint->optionForUser($this->user('admin'), 'changeTemplate')
		);
	}
}
