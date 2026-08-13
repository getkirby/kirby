<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteValidators::class)]
class SiteValidatorsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.SiteValidators';

	public function testEnsureChangeTitle(): void
	{
		$validators = $this->validators(new Site());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.site.changeTitle.empty');

		$validators->ensure('changeTitle', '');
	}

	public function testTitle(): void
	{
		$validators = $this->validators(new Site());

		$this->assertNull($validators->validateTitle('Test'));
	}

	public function testTitleWithEmptyTitle(): void
	{
		$validators = $this->validators(new Site());

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.site.changeTitle.empty');

		$validators->validateTitle('');
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}

	protected function validators(Site $site): SiteValidators
	{
		return new SiteValidators(
			model: $site,
			user: $this->user()
		);
	}
}
