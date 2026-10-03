<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class LanguageRoutesTest extends TestCase
{
	protected function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US.UTF-8',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_AT.UTF-8',
					'url'     => '/de',
				],
			]
		]);
	}

	protected function tearDown(): void
	{
		Uri::$current = null;
	}

	public function testFallback(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'error',
						'template' => 'error'
					]
				]
			]
		]);

		$app->call('notes');
		$this->assertSame($app->language()->code(), 'en');

		$app->call('de/notes');
		$this->assertSame($app->language()->code(), 'de');
	}

	public function testFallbackRedirect(): void
	{
		Uri::$current = new Uri('https://getkirby.com/notes/tag:foo?bar=baz');

		$app = $this->app->clone([
			'languages' => [['url' => '/en']],
			'site'      => ['children' => [['slug' => 'notes']]],
			'urls'      => ['index' => 'https://getkirby.com']
		]);

		$response = $app->call('notes');

		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame(
			'https://getkirby.com/en/notes/tag:foo?bar=baz',
			$response->header('Location')
		);
	}

	public function testFallbackRedirectWithDetection(): void
	{
		Uri::$current = new Uri('https://getkirby.com/notes/tag:foo?bar=baz');

		$app = $this->app->clone([
			'languages' => [['url' => '/en']],
			'options'   => ['languages.detect' => true],
			'site'      => [
				'children' => [
					[
						'slug'         => 'notes',
						'translations' => [
							['code' => 'en', 'content' => ['title' => 'Notes']],
							['code' => 'de', 'content' => ['title' => 'Notizen']]
						]
					]
				]
			],
			'urls' => ['index' => 'https://getkirby.com']
		]);

		$app->visitor()->acceptedLanguage('de');
		$response = $app->call('notes');

		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame(
			'https://getkirby.com/de/notes/tag:foo?bar=baz',
			$response->header('Location')
		);
	}

	public static function homeRedirectProvider(): array
	{
		return [
			'default language'  => [false, 'https://getkirby.com/en/tag:foo?bar=baz'],
			'detected language' => [true, 'https://getkirby.com/de/tag:foo?bar=baz'],
		];
	}

	#[DataProvider('homeRedirectProvider')]
	public function testHomeRedirect(bool $detect, string $expected): void
	{
		Uri::$current = new Uri('https://getkirby.com/tag:foo?bar=baz');

		$app = $this->app->clone([
			'languages' => [['url' => '/en']],
			'options'   => ['languages.detect' => $detect],
			'urls'      => ['index' => 'https://getkirby.com']
		]);

		$app->visitor()->acceptedLanguage('de');
		$response = $app->call('');

		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame($expected, $response->header('Location'));
	}

	public function testNotNextWhenFalsyReturn(): void
	{
		$a = $b = $c = $d = $e = 0;

		$app = $this->app->clone([
			'options' => [
				'routes' => [
					[
						'pattern' => 'route-a',
						'action'  => function () use (&$a) {
							$a++;
							return false;
						},
					],
					[
						'pattern' => 'route-b',
						'language' => '*',
						'action'  => function ($language) use (&$b) {
							$b++;
							return false;
						},
					],
					[
						'pattern' => 'route-c',
						'language' => 'en',
						'action'  => function ($language) use (&$c) {
							$c++;
							return false;
						},
					],
					[
						'pattern' => 'route-d',
						'language' => 'de',
						'action'  => function ($language) use (&$d) {
							$d++;
							return false;
						},
					],
					[
						'pattern' => 'route-e',
						'language' => '*',
						'action'  => function ($language) use (&$e) {
							$e++;
							return null;
						},
					],
				],
			]
		]);

		$this->assertSame(0, $a);
		$this->assertSame(0, $b);
		$this->assertSame(0, $c);
		$this->assertSame(0, $d);
		$this->assertSame(0, $e);

		$app->call('route-a');
		$app->call('route-b');
		$app->call('route-c');
		$app->call('de/route-d');
		$app->call('route-e');

		$this->assertSame(1, $a);
		$this->assertSame(1, $b);
		$this->assertSame(1, $c);
		$this->assertSame(1, $d);
		$this->assertSame(2, $e);
	}
}
