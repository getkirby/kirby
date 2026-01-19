<?php

namespace Kirby\Toolkit;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\TestCase;

class HasStringTemplateMock
{
	use HasStringTemplate;

	public function __construct(private ModelWithContent|null $model)
	{
	}

	public function i18nPublic(...$args): string|null
	{
		return $this->i18n(...$args);
	}

	public function model(): ModelWithContent|null
	{
		return $this->model;
	}

	public function stringTemplatePublic(...$args): string|null
	{
		return $this->stringTemplate(...$args);
	}

	public function stringTemplateI18nPublic(...$args): string|null
	{
		return $this->stringTemplateI18n(...$args);
	}
}

class HasStringTemplateTest extends TestCase
{
	public function testStringTemplate(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => '<b>Test</b>'
			]
		]);

		$class = new HasStringTemplateMock($page);

		$this->assertSame('Title: &lt;b&gt;Test&lt;/b&gt;', $class->stringTemplatePublic('Title: {{ page.title }}'));
		$this->assertSame('Title: <b>Test</b>', $class->stringTemplatePublic('Title: {{ page.title }}', safe: false));
	}

	public function testUsesHasI18nTrait(): void
	{
		I18n::$translations['en'] = [
			'my.key' => 'My translation',
		];

		$class = new HasStringTemplateMock(null);
		$this->assertSame('My translation', $class->i18nPublic('my.key'));
	}

	public function testStringTemplateWithoutModel(): void
	{
		$class = new HasStringTemplateMock(null);
		$this->assertSame('Title: {{ page.title }}', $class->stringTemplatePublic('Title: {{ page.title }}'));
	}

	public function testStringTemplateI18n(): void
	{
		$page  = new Page(['slug' => 'test']);
		$class = new HasStringTemplateMock($page);

		$this->assertSame(
			'Link: test',
			$class->stringTemplateI18nPublic(['en' => 'Link: {{ page.slug }}'])
		);
	}
}
