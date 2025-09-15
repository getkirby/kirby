<?php

namespace Kirby\Cms;

class SearchTest extends TestCase
{
	public function testCollection(): void
	{
		$collection = Pages::factory([
			[
				'slug'    => 'homer',
				'content' => ['name' => 'Homer']
			],
			[
				'slug'    => 'marge',
				'content' => ['name' => 'Marge']
			],
			[
				'slug'    => 'maggie',
				'content' => ['name' => 'Maggie']
			],
			[
				'slug'    => 'lisa',
				'content' => ['name' => 'Lisa']
			]
		]);

		$search = Search::collection($collection, 'ma');
		$this->assertCount(2, $search);

		$search = Search::collection($collection, 'Ho');
		$this->assertCount(1, $search);

		$search = Search::collection($collection, 'm', ['minlength' => 1]);
		$this->assertCount(3, $search);

		$search = Search::collection($collection, 'm', ['minlength' => 2]);
		$this->assertCount(0, $search);

		$search = Search::collection($collection, ' ');
		$this->assertCount(0, $search);

		$search = Search::collection($collection, null);
		$this->assertCount(0, $search);

		$search = Search::collection($collection);
		$this->assertCount(0, $search);
	}


	public function testIgnoreFieldCase(): void
	{
		$collection = Pages::factory([
			[
				'slug'    => 'homer',
				'content' => ['firstname' => 'Homer']
			],
			[
				'slug'    => 'marge',
				'content' => ['firstname' => 'Marge']
			],
			[
				'slug'    => 'maggie',
				'content' => ['firstname' => 'Maggie']
			],
			[
				'slug'    => 'lisa',
				'content' => ['firstname' => 'Lisa']
			],
			[
				'slug'    => 'snowball',
				'content' => ['firstname' => 'Šnowball']
			]
		]);

		$search = Search::collection($collection, 'ma', ['fields' => ['FirstName']]);
		$this->assertCount(2, $search);

		$search = Search::collection($collection, 'Ho', ['fields' => ['FirstName']]);
		$this->assertCount(1, $search);

		$search = Search::collection($collection, 'm', ['minlength' => 1, 'fields' => ['FirstName']]);
		$this->assertCount(3, $search);
	}

	public function testIgnoreCaseI18n(): void
	{
		$collection = Pages::factory([
			[
				'slug'    => 'santa',
				'content' => ['full' => 'Santa\'s Little Helper']
			],
			[
				'slug'    => 'snowball',
				'content' => ['full' => 'Šnowball']
			],
			[
				'slug'    => 'garfield',
				'content' => ['full' => 'Garfield']
			]
		]);

		$search = Search::collection($collection, 's', ['minlength' => 1]);
		$this->assertCount(2, $search);
		$search = Search::collection($collection, 'S', ['minlength' => 1]);
		$this->assertCount(2, $search);

		$search = Search::collection($collection, 'š', ['minlength' => 1]);
		$this->assertCount(1, $search);
		$search = Search::collection($collection, 'Š', ['minlength' => 1]);
		$this->assertCount(1, $search);

		$search = Search::collection($collection, 'g', ['minlength' => 1]);
		$this->assertCount(1, $search);
		$search = Search::collection($collection, 'G', ['minlength' => 1]);
		$this->assertCount(1, $search);
	}

	public function app(): App
	{
		return new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'categories',
						'files' => [
							['filename' => 'phone.jpg'],
							['filename' => 'cell-phone.jpg'],
							['filename' => 'computer.jpg']
						]
					],
					[
						'slug'  => 'products',
						'files' => [
							['filename' => 'apple.jpg'],
							['filename' => 'samsung.jpg']
						]
					],
					[
						'slug' => 'contact'
					]
				],
				'files' => [
					['filename' => 'website.jpg']
				]
			],
			'users' => [
				['email' => 'admin@getkirby.com'],
				['email' => 'editor@getkirby.com'],
				['email' => 'user1@getkirby.com'],
				['email' => 'user2@getkirby.com'],
				['email' => 'user3@getkirby.com']
			],
		]);
	}

	public function testFiles(): void
	{
		$this->assertCount(5, $this->app()->site()->index()->files());
		$this->assertInstanceOf(Files::class, $files = Search::files('phone'));
		$this->assertCount(2, $files);
	}

	public function testPages(): void
	{
		$this->assertCount(3, $this->app()->site()->index());
		$this->assertInstanceOf(Pages::class, $pages = Search::pages('products'));
		$this->assertCount(1, $pages);
	}

	public function testUsers(): void
	{
		$this->assertCount(5, $this->app()->users());
		$this->assertInstanceOf(Users::class, $users = Search::users('user'));
		$this->assertCount(3, $users);
	}
}
