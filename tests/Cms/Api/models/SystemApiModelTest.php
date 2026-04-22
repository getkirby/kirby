<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class SystemApiModelTest extends ApiModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SystemApiModel';

	public function testTranslationWithInaccessibleUser(): void
	{
		static::setUpTmp();

		$uuid = uuid();

		$this->app = new App([
			'options' => [
				'api' => [
					'allowImpersonation' => true,
				],
			],
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'users/restricted-' . $uuid => [
					'options' => ['access' => false],
				],
			],
			'roles' => [
				['name' => 'restricted-' . $uuid],
			],
			'users' => [
				[
					'email'    => 'restricted@example.com',
					'language' => 'de',
					'role'     => 'restricted-' . $uuid,
				],
			],
		]);

		$this->api = $this->app->api();
		$this->app->impersonate('restricted@example.com');

		$system      = $this->app->system();
		$translation = $this->api->resolve($system)->select('translation')->toArray()['translation'];

		$this->assertIsArray($translation);
		$this->assertSame('de', $translation['id']);
		$this->assertArrayHasKey('data', $translation);
	}
}
