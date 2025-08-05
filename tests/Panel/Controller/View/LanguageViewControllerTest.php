<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Language;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Stats;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageViewController::class)]
class LanguageViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.LanguageViewController';

	protected Language $language;

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
				],
				'de' => [
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		$this->language = $this->app->language('en');
	}

	public function testBreadcrumb(): void
	{
		$controller = new LanguageViewController($this->language);
		$breadcrumb = $controller->breadcrumb();
		$this->assertCount(1, $breadcrumb);
		$this->assertSame('English', $breadcrumb[0]['label']);
	}

	public function testButtons(): void
	{
		$controller = new LanguageViewController($this->language);
		$buttons = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(3, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = LanguageViewController::factory('en');
		$this->assertInstanceOf(LanguageViewController::class, $controller);
		$this->assertSame('en', $controller->language->code());
	}

	public function testInfo(): void
	{
		$controller = new LanguageViewController($this->language);
		$info       = $controller->info();
		$this->assertInstanceOf(Stats::class, $info);

		$reports = $info->reports();
		$this->assertCount(4, $reports);
		$this->assertSame('Default language', $reports[0]['value']);
		$this->assertSame('en', $reports[1]['value']);
		$this->assertSame('en', $reports[2]['value']);
		$this->assertSame('Left to right', $reports[3]['value']);
	}

	public function testLoad(): void
	{
		$controller = new LanguageViewController($this->language);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-language-view', $view->component);
		$this->assertSame('English', $view->title);

		$props = $view->props();
		$this->assertSame('en', $props['code']);
		$this->assertSame('ltr', $props['direction']);
		$this->assertSame('English', $props['name']);
		$this->assertSame('/en', $props['url']);
	}

	public function testNext(): void
	{
		$language   = $this->app->language('en');
		$controller = new LanguageViewController($language);
		$next       = $controller->next();
		$this->assertSame([
			'link'  => '/languages/de',
			'title' => 'Deutsch',
		], $next);

		$language   = $this->app->language('de');
		$controller = new LanguageViewController($language);
		$next       = $controller->next();
		$this->assertNull($next);
	}

	public function testPrev(): void
	{
		$language   = $this->app->language('en');
		$controller = new LanguageViewController($language);
		$prev       = $controller->prev();
		$this->assertNull($prev);

		$language   = $this->app->language('de');
		$controller = new LanguageViewController($language);
		$prev       = $controller->prev();
		$this->assertSame([
			'link'  => '/languages/en',
			'title' => 'English',
		], $prev);
	}

	public function testTranslations(): void
	{
		$controller = new LanguageViewController($this->language);
		$translations = $controller->translations();
		$this->assertCount(0, $translations);

		$this->app = $this->app->clone([
			'languages' => [
				'en' => [
					'code' => 'en',
					'name' => 'English',
					'translations' => [
						'test' => 'Test',
					]
				]
			]
		]);

		$controller = new LanguageViewController($this->app->language('en'));
		$translations = $controller->translations();
		$this->assertCount(1, $translations);
		$this->assertSame('test', $translations[0]['key']);
		$this->assertSame('Test', $translations[0]['value']);
		$this->assertCount(2, $translations[0]['options']);
		$this->assertTrue($translations[0]['options'][0]['disabled']);

		$this->app->impersonate('kirby');
		$translations = $controller->translations();
		$this->assertFalse($translations[0]['options'][0]['disabled']);
	}

}
