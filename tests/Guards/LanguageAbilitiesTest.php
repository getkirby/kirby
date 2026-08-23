<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageAbilities::class)]
class LanguageAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.LanguageAbilities';

	public function testDeleteWithSingleLanguageObject(): void
	{
		$abilities = new LanguageAbilities(
			model: Language::single(),
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.language.delete.single');

		$abilities->ensure('delete');
	}

	public function testDeleteWithDefaultLanguage(): void
	{
		$this->setUpMultiLanguage();

		$abilities = new LanguageAbilities(
			model: $this->app->language('en'),
			user: $this->user()
		);

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.language.delete.default');

		$abilities->ensure('delete');
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

		$abilities = new LanguageAbilities(
			model: $this->app->language('en'),
			user: $this->user()
		);

		$this->assertNull($abilities->ensure('delete'));
	}

	public function testDeleteWithTranslation(): void
	{
		$this->setUpMultiLanguage();

		$abilities = new LanguageAbilities(
			model: $this->app->language('de'),
			user: $this->user()
		);

		$this->assertNull($abilities->ensure('delete'));
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}
}
