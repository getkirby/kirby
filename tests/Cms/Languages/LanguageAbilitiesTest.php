<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageAbilities::class)]
class LanguageAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageAbilities';

	public function testDeleteWithSingleLanguageObject(): void
	{
		$abilities = new LanguageAbilities(Language::single());

		$this->assertFalse($abilities->delete());
	}

	public function testDeleteWithDefaultLanguage(): void
	{
		$this->setUpMultiLanguage();

		$abilities = new LanguageAbilities($this->app->language('en'));

		$this->assertFalse($abilities->delete());
	}

	public function testDeleteWithLastDefaultLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				]
			]
		]);

		$abilities = new LanguageAbilities($this->app->language('en'));

		$this->assertTrue($abilities->delete());
	}

	public function testDeleteWithTranslation(): void
	{
		$this->setUpMultiLanguage();

		$abilities = new LanguageAbilities($this->app->language('de'));

		$this->assertTrue($abilities->delete());
	}

	public function testInheritedAbilities(): void
	{
		$this->setUpMultiLanguage();

		$abilities = new LanguageAbilities($this->app->language('en'));

		$this->assertTrue($abilities->create());
		$this->assertTrue($abilities->update());
	}
}
