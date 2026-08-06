<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageValidators::class)]
class LanguageValidatorsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.LanguageValidators';

	public function testCode(): void
	{
		$validators = $this->validators(new Language(['code' => 'en']));

		$this->assertNull($validators->validateCode('en'));
	}

	public function testCodeWithInvalidCode(): void
	{
		$validators = $this->validators(new Language(['code' => 'e']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.language.code');

		$validators->validateCode('e');
	}

	public function testDemotion(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'de', 'name' => 'Deutsch', 'default' => true],
				['code' => 'en', 'name' => 'English']
			]
		]);

		$oldLanguage = $this->app->language('en');
		$newLanguage = new Language(['code' => 'en', 'name' => 'English']);

		$validators = $this->validators($newLanguage);

		$this->assertNull($validators->validateDemotion($oldLanguage));
		$this->assertNull($validators->validateDemotion());
	}

	public function testDemotionWithDemotedDefaultLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'de', 'name' => 'Deutsch', 'default' => true],
				['code' => 'en', 'name' => 'English']
			]
		]);

		$oldLanguage = $this->app->language('de');
		$newLanguage = new Language([
			'code'    => 'de',
			'name'    => 'Deutsch',
			'default' => false
		]);

		$validators = $this->validators($newLanguage);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Please select another language to be the primary language');

		$validators->validateDemotion($oldLanguage);
	}

	public function testDoesNotExist(): void
	{
		$validators = $this->validators(new Language(['code' => 'de']));

		$this->assertNull($validators->validateDoesNotExist());
	}

	public function testDoesNotExistWithExistingLanguage(): void
	{
		$language   = new Language(['code' => 'de']);
		$validators = $this->validators($language);

		F::write($language->root(), '<?php return [];');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.language.duplicate');

		$validators->validateDoesNotExist();
	}

	public function testEnsureUpdate(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'de', 'name' => 'Deutsch', 'default' => true],
				['code' => 'en', 'name' => 'English']
			]
		]);

		$oldLanguage = $this->app->language('en');
		$newLanguage = new Language(['code' => 'en', 'name' => 'English']);

		$validators = $this->validators($newLanguage);

		$this->assertNull($validators->ensure('update', $oldLanguage));
	}

	public function testEnsureUpdateWithDemotedDefaultLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'de', 'name' => 'Deutsch', 'default' => true],
				['code' => 'en', 'name' => 'English']
			]
		]);

		$oldLanguage = $this->app->language('de');
		$newLanguage = new Language([
			'code'    => 'de',
			'name'    => 'Deutsch',
			'default' => false
		]);

		$validators = $this->validators($newLanguage);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Please select another language to be the primary language');

		$validators->ensure('update', $oldLanguage);
	}

	public function testEnsureUpdateWithInvalidName(): void
	{
		$validators = $this->validators(new Language([
			'code' => 'en',
			'name' => ''
		]));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.language.name');

		$validators->ensure('update');
	}

	public function testName(): void
	{
		$validators = $this->validators(new Language(['code' => 'en']));

		$this->assertNull($validators->validateName('English'));
	}

	public function testNameWithInvalidName(): void
	{
		$validators = $this->validators(new Language(['code' => 'en']));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.language.name');

		$validators->validateName('');
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}

	protected function validators(Language $language): LanguageValidators
	{
		return new LanguageValidators(
			model: $language,
			user: $this->user()
		);
	}
}
