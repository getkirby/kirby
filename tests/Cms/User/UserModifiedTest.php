<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserModifiedTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserModified';

	public function testModified(): void
	{
		// create a user file
		F::write($file = static::TMP . '/site/accounts/test/index.php', '<?php return [];');

		$modified = filemtime($file);
		$user     = $this->app->user('test');

		$this->assertSame((string)$modified, $user->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $user->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $user->modified($format, 'strftime'));
	}

	public function testModifiedSpecifyingLanguage(): void
	{
		$this->setUpMultiLanguage();

		// create a user file
		F::write($file = static::TMP . '/site/accounts/test/index.php', '<?php return [];');

		// create the english page
		F::write($file = static::TMP . '/site/accounts/test/user.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german page
		F::write($file = static::TMP . '/site/accounts/test/user.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$user = $this->app->user('test');

		$this->assertSame((string)$modifiedEnContent, $user->modified('U', null, 'en'));
		$this->assertSame((string)$modifiedDeContent, $user->modified('U', null, 'de'));
	}
}
