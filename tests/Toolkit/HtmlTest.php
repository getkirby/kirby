<?php

namespace Kirby\Toolkit;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Html::class)]
class HtmlTest extends TestCase
{
	public function testCallStatic(): void
	{
		$this->assertSame('<div>test</div>', Html::div('test'));
		$this->assertSame('<div class="test">test</div>', Html::div('test', ['class' => 'test']));
		$this->assertSame('<hr class="test">', Html::hr(['class' => 'test']));
	}

	public function testA(): void
	{
		$html = Html::a('https://getkirby.com');
		$expected = '<a href="https://getkirby.com">getkirby.com</a>';
		$this->assertSame($expected, $html);

		$html = Html::a('mailto:mail@company.com');
		$expected = '!^<a href="mailto:(.*?)">(.*?)</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));
		$this->assertSame('mail@company.com', Html::decode($matches[2]));

		$html = Html::a('tel:1234');
		$expected = '<a href="tel:1234">1234</a>';
		$this->assertSame($expected, $html);
	}

	public function testAWithText(): void
	{
		$html = Html::a('https://getkirby.com', 'Kirby');
		$expected = '<a href="https://getkirby.com">Kirby</a>';
		$this->assertSame($expected, $html);

		$html = Html::a('mailto:mail@company.com', 'Kirby');
		$expected = '!^<a href="mailto:(.*?)">Kirby</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));

		$html = Html::a('tel:1234', 'Kirby');
		$expected = '<a href="tel:1234">Kirby</a>';
		$this->assertSame($expected, $html);
	}

	public function testAWithAttributes(): void
	{
		$html = Html::a('https://getkirby.com', 'Kirby', ['class' => 'test']);
		$expected = '<a class="test" href="https://getkirby.com">Kirby</a>';
		$this->assertSame($expected, $html);

		$html = Html::a('mailto:mail@company.com', 'Kirby', ['class' => 'test']);
		$expected = '!^<a class="test" href="mailto:(.*?)">Kirby</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));

		$html = Html::a('tel:1234', 'Kirby', ['class' => 'test']);
		$expected = '<a class="test" href="tel:1234">Kirby</a>';
		$this->assertSame($expected, $html);
	}

	public function testAWithTarget(): void
	{
		$html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank']);
		$expected = '<a href="https://getkirby.com" rel="noreferrer" target="_blank">Kirby</a>';
		$this->assertSame($expected, $html);
	}

	public function testAWithTargetAndRel(): void
	{
		$html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank', 'rel' => 'noopener']);
		$expected = '<a href="https://getkirby.com" rel="noopener" target="_blank">Kirby</a>';
		$this->assertSame($expected, $html);
	}

	#[DataProvider('attrProvider')]
	public function testAttr(
		array $input,
		bool|null $value,
		string|null $expected
	): void {
		$this->assertSame($expected, Html::attr($input, $value));
	}

	public static function attrProvider(): array
	{
		return [
			[[],                         null,  null],
			[['B' => 'b', 'A' => 'a'],   null,  'a="a" b="b"'],
			[['B' => 'b', 'A' => 'a'],   true,  'a="a" b="b"'],
			[['B' => 'b', 'A' => 'a'],   false, 'b="b" a="a"'],
			[['a' => 'a', 'b' => true],  null,  'a="a" b'],
			[['a' => 'a', 'b' => ''],    null,  'a="a" b=""'],
			[['a' => 'a', 'b' => false], null,  'a="a"'],
			[['a' => 'a', 'b' => null],  null,  'a="a"'],
			[['a' => 'a', 'b' => []],    null,  'a="a"'],
			[['a', 'b' => true],         null,  'a b']
		];
	}

	public function testAttrArrayValue(): void
	{
		$result = Html::attr('a', ['a', 'b']);
		$this->assertSame('a="a b"', $result);

		$result = Html::attr('a', ['a', 1]);
		$this->assertSame('a="a 1"', $result);

		$result = Html::attr('a', ['a', null]);
		$this->assertSame('a="a"', $result);

		$result = Html::attr('a', ['value' => '&', 'escape' => true]);
		$this->assertSame('a="&amp;"', $result);

		$result = Html::attr('a', ['value' => '&', 'escape' => false]);
		$this->assertSame('a="&"', $result);
	}

	public function testAttrWithBeforeValue(): void
	{
		$attr = Html::attr(['test' => 'test'], null, ' ');
		$this->assertSame(' test="test"', $attr);
	}

	public function testAttrWithAfterValue(): void
	{
		$attr = Html::attr(['test' => 'test'], null, null, ' ');
		$this->assertSame('test="test" ', $attr);
	}

	public function testAttrWithoutValues(): void
	{
		$attr = Html::attr([]);
		$this->assertNull($attr);
	}

	public function testBreaks(): void
	{
		$this->assertSame("line 1<br />\nline 2", Html::breaks("line 1\nline 2"));
	}

	public function testEmail(): void
	{
		$html = Html::email('mail@company.com?subject=Test');
		$expected = '!^<a href="mailto:(.*?)">(.*?)</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com?subject=Test', Html::decode($matches[1]));
		$this->assertSame('mail@company.com', Html::decode($matches[2]));
	}

	public function testEmailWithText(): void
	{
		$html = Html::email('mail@company.com', '<b>Email</b>');
		$expected = '!^<a href="mailto:(.*?)">&lt;b&gt;Email&lt;/b&gt;</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));
	}

	public function testEmailWithArrayText(): void
	{
		$html = Html::email('mail@company.com', ['<b>Email</b>']);
		$expected = '!^<a href="mailto:(.*?)"><b>Email</b></a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));
	}

	public function testEmailWithoutAddress(): void
	{
		$html = Html::email('');
		$this->assertSame('', $html);
	}

	public function testEmailWithAttributes(): void
	{
		$html = Html::email('mail@company.com', 'Email', ['class' => 'email']);
		$expected = '!^<a class="email" href="mailto:(.*?)">Email</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));
	}

	public function testEmailWithTarget(): void
	{
		$html = Html::email('mail@company.com', 'Email', ['target' => '_blank']);
		$expected = '!^<a href="mailto:(.*?)" rel="noreferrer" target="_blank">Email</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com', Html::decode($matches[1]));
	}

	public function testEncode(): void
	{
		$html = Html::encode('äöü');
		$expected = '&auml;&ouml;&uuml;';
		$this->assertSame($expected, $html);

		$html = Html::encode('ä<p>ö</p>');
		$expected = '&auml;&lt;p&gt;&ouml;&lt;/p&gt;';
		$this->assertSame($expected, $html);

		$html = Html::encode('ä<span title="Amazing &amp; great">ö</span>', true);
		$expected = '&auml;<span title="Amazing &amp; great">&ouml;</span>';
		$this->assertSame($expected, $html);

		$this->assertSame('', Html::encode(''));
		$this->assertSame('', Html::encode(null));
	}

	public function testEntities(): void
	{
		Html::$entities = null;
		$this->assertTrue(count(Html::entities()) > 0);

		Html::$entities = [];
		$this->assertSame([], Html::entities());

		Html::$entities = ['t' => 'test'];
		$this->assertSame(['t' => 'test'], Html::entities());

		Html::$entities = null;
	}

	public function testFigure(): void
	{
		$html = Html::figure('test');
		$expected = '<figure>test</figure>';
		$this->assertSame($expected, $html);

		$html = Html::figure('test', '', ['class' => 'figure']);
		$expected = '<figure class="figure">test</figure>';
		$this->assertSame($expected, $html);

		$html = Html::figure('test', 'yay');
		$expected = '<figure>test<figcaption>yay</figcaption></figure>';
		$this->assertSame($expected, $html);
	}

	public function testGist(): void
	{
		$html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff');
		$expected = '<script src="' . $url . '.js"></script>';
		$this->assertSame($expected, $html);

		$html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff', 'kirbycontent.txt');
		$expected = '<script src="' . $url . '.js?file=kirbycontent.txt"></script>';
		$this->assertSame($expected, $html);
	}

	public function testIframe(): void
	{
		$html = Html::iframe($url = 'https://getkirby.com');
		$expected = '<iframe src="' . $url . '"></iframe>';
		$this->assertSame($expected, $html);
	}

	public function testImg(): void
	{
		$html = Html::img($src = 'https://getkirby.com/image.jpg');
		$expected = '<img alt="" src="' . $src . '">';
		$this->assertSame($expected, $html);
	}

	public function testIsVoid(): void
	{
		$original = Html::$voidList;

		$this->assertTrue(Html::isVoid('hr'));
		$this->assertFalse(Html::isVoid('div'));
		$this->assertFalse(Html::isVoid(''));

		Html::$voidList[] = 'div';
		$this->assertTrue(Html::isVoid('div'));

		Html::$voidList = $original;
	}

	public function testRel(): void
	{
		$html = Html::rel('me');
		$expected = 'me';
		$this->assertSame($expected, $html);

		$html = Html::rel(null, '_blank');
		$expected = 'noreferrer';
		$this->assertSame($expected, $html);

		$html = Html::rel('noopener', '_blank');
		$expected = 'noopener';
		$this->assertSame($expected, $html);
	}

	public function testTel(): void
	{
		$html = Html::tel('1234');
		$expected = '<a href="tel:1234">1234</a>';
		$this->assertSame($expected, $html);
	}

	public function testTelWithText(): void
	{
		$html = Html::tel('1234', 'Tel');
		$expected = '<a href="tel:1234">Tel</a>';
		$this->assertSame($expected, $html);
	}

	public function testTelWithArrayText(): void
	{
		$html = Html::tel('1234', ['<b>Tel</b>']);
		$expected = '<a href="tel:1234"><b>Tel</b></a>';
		$this->assertSame($expected, $html);
	}

	public function testTag(): void
	{
		$html = Html::tag('p', 'test');
		$expected = '<p>test</p>';
		$this->assertSame($expected, $html);

		$html = Html::tag('p', '');
		$expected = '<p></p>';
		$this->assertSame($expected, $html);

		$html = Html::tag('p', null);
		$expected = '<p></p>';
		$this->assertSame($expected, $html);

		$html = Html::tag('hr', '');
		$expected = '<hr>';
		$this->assertSame($expected, $html);

		$html = Html::tag('hr', null);
		$expected = '<hr>';
		$this->assertSame($expected, $html);

		Html::$void = ' />';
		$html = Html::tag('hr', null);
		$expected = '<hr />';
		$this->assertSame($expected, $html);
		Html::$void = '>';

		$html = Html::tag('p', 'test', ['class' => 'test']);
		$expected = '<p class="test">test</p>';
		$this->assertSame($expected, $html);

		$html = Html::tag('p', 'täst', ['class' => 'test']);
		$expected = '<p class="test">t&auml;st</p>';
		$this->assertSame($expected, $html);

		$html = Html::tag('p', ['<i>test</i>']);
		$expected = '<p><i>test</i></p>';
		$this->assertSame($expected, $html);
	}

	#[DataProvider('valueProvider')]
	public function testValue(
		bool|int|string|null $input,
		string|null $expected
	): void {
		$this->assertSame($expected, Html::value($input));
	}

	public static function valueProvider(): array
	{
		return [
			[true, 'true'],
			[false, 'false'],
			[1, '1'],
			[null, null],
			['', null],
			['test', 'test'],
			['täst', 't&auml;st'],
		];
	}

	#[DataProvider('videoProvider')]
	public function testVideo(
		string $url,
		string|bool $src
	): void {
		// invalid URLs
		if ($src === false) {
			$this->assertNull(Html::video($url));
			return;
		}

		// plain
		$html = Html::video($url);
		$expected = '<iframe allow="fullscreen" allowfullscreen src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);

		// with attributes
		$html = Html::video($url, [], ['class' => 'video']);
		$expected = '<iframe allow="fullscreen" allowfullscreen class="video" src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);

		// with options
		$options = [
			'vimeo'   => ['foo' => 'bar'],
			'youtube' => ['foo' => 'bar']
		];
		$html = Html::video($url, $options);
		$char = Str::contains($src, '?') === true ? '&amp;' : '?';
		$expected = '<iframe allow="fullscreen" allowfullscreen src="' . $src . $char . 'foo=bar"></iframe>';
		$this->assertSame($expected, $html);

		// with attributes and options
		$options = [
			'vimeo'   => ['foo' => 'bar'],
			'youtube' => ['foo' => 'bar']
		];
		$html = Html::video($url, $options, ['class' => 'video']);
		$expected = '<iframe allow="fullscreen" allowfullscreen class="video" src="' . $src . $char . 'foo=bar"></iframe>';
		$this->assertSame($expected, $html);

		// allow attribute
		$html = Html::video($url, [], ['allow' => 'camera \'none\'; microphone \'none\'']);
		$expected = '<iframe allow="camera &#039;none&#039;; microphone &#039;none&#039;" src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);

		// allow fullscreen enabled
		$html = Html::video($url, [], ['allow' => 'fullscreen']);
		$expected = '<iframe allow="fullscreen" src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);

		// legacy allow fullscreen enabled
		$html = Html::video($url, [], ['allowfullscreen' => true]);
		$expected = '<iframe allow="fullscreen" allowfullscreen src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);

		// legacy allow fullscreen disabled
		$html = Html::video($url, [], ['allowfullscreen' => false]);
		$expected = '<iframe src="' . $src . '"></iframe>';
		$this->assertSame($expected, $html);
	}

	public function testVideoFile(): void
	{
		$html = Html::video('https://getkirby.com/myvideo.mp4');
		$expected = '<video><source src="https://getkirby.com/myvideo.mp4" type="video/mp4"></video>';
		$this->assertSame($expected, $html);

		// with attributes
		$html = Html::video('https://getkirby.com/myvideo.mp4', [], ['controls' => true, 'autoplay' => true]);
		$expected = '<video autoplay controls><source src="https://getkirby.com/myvideo.mp4" type="video/mp4"></video>';
		$this->assertSame($expected, $html);

		// relative path
		$html = Html::video('../myvideo.mp4');
		$expected = '<video><source src="../myvideo.mp4" type="video/mp4"></video>';
		$this->assertSame($expected, $html);

		// invalid file type
		$html = Html::video('https://getkirby.com/myvideo.mp3');
		$this->assertNull($html);
	}

	public static function videoProvider(): array
	{
		return [
			// YouTube
			[
				'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'http://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'http://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube-nocookie.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'http://www.youtube.com/embed/d9NF2edxy-M',
				'https://www.youtube.com/embed/d9NF2edxy-M'
			],
			[
				'http://www.youtube.com/shorts/z-zDhFM_oAo',
				'https://www.youtube.com/embed/z-zDhFM_oAo'
			],
			[
				'http://www.youtube.com/embed/d9NF2edxy-M?start=10',
				'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'http://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube.com/embed/d9NF2edxy-M?start=10&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M',
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
			],
			[
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10',
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M',
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
			],
			[
				'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M&t=10',
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'https://www.youtube-nocookie.com/watch?test=value&v=d9NF2edxy-M&t=10',
				'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&amp;start=10'
			],
			[
				'https://www.youtube-nocookie.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'https://www.youtube-nocookie.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube-nocookie.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'http://www.youtube.com/watch?v=d9NF2edxy-M',
				'https://www.youtube.com/embed/d9NF2edxy-M'
			],
			[
				'http://www.youtube.com/watch?test=value&v=d9NF2edxy-M',
				'https://www.youtube.com/embed/d9NF2edxy-M?test=value'
			],
			[
				'http://www.youtube.com/watch?v=d9NF2edxy-M&t=10',
				'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'https://www.youtube.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'https://www.youtube.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
				'https://www.youtube.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
			],
			[
				'https://www.youtu.be/d9NF2edxy-M',
				'https://www.youtube.com/embed/d9NF2edxy-M'
			],
			[
				'https://www.youtu.be/d9NF2edxy-M?t=10',
				'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'https://youtu.be/d9NF2edxy-M?t=10',
				'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
			],
			[
				'https://www.youtu.be/d9NF2edxy-M?test=value&t=10',
				'https://www.youtube.com/embed/d9NF2edxy-M?test=value&amp;start=10'
			],

			// Vimeo
			[
				'https://vimeo.com/239882943',
				'https://player.vimeo.com/video/239882943'
			],
			[
				'https://vimeo.com/channels/channelname/239882943',
				'https://player.vimeo.com/video/239882943'
			],
			[
				'https://vimeo.com/groups/groupname/videos/239882943',
				'https://player.vimeo.com/video/239882943'
			],
			[
				'https://vimeo.com/showcase/239882943',
				'https://player.vimeo.com/video/239882943'
			],
			[
				'https://player.vimeo.com/video/239882943',
				'https://player.vimeo.com/video/239882943'
			],
			[
				'https://vimeo.com/239882943?test=value',
				'https://player.vimeo.com/video/239882943?test=value'
			],
			[
				'https://vimeo.com/channels/channelname/239882943?test=value',
				'https://player.vimeo.com/video/239882943?test=value'
			],
			[
				'https://vimeo.com/groups/groupname/videos/239882943?test=value',
				'https://player.vimeo.com/video/239882943?test=value'
			],
			[
				'https://vimeo.com/showcase/239882943?test=value',
				'https://player.vimeo.com/video/239882943?test=value'
			],
			[
				'https://player.vimeo.com/video/239882943?test=value',
				'https://player.vimeo.com/video/239882943?test=value'
			],

			// invalid URLs
			[
				'https://getkirby.com',
				false
			],
			[
				'https://youtube.com/imprint',
				false
			],
			[
				'https://www.youtu.be',
				false
			],
			[
				'https://www.youtube.com/watch?list=zv=21HuwjmuS7A&index=1',
				false
			],
			[
				'https://youtube.com/watch?v=öööö',
				false
			],
			[
				'https://vimeo.com',
				false
			],
			[
				'https://vimeo.com/öööö',
				false
			]
		];
	}

	public function testVimeoInvalidUrl(): void
	{
		$this->assertNull(Html::vimeo('https://getkirby.com'));
	}

	public function testYoutubeInvalidUrl(): void
	{
		$this->assertNull(Html::youtube('https://getkirby.com'));
	}
}
