<?php

namespace Kirby\Http;

use Kirby\TestCase;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

class VisitorTest extends TestCase
{
	public function testVisitorDefaults(): void
	{
		$visitor = new Visitor();

		$this->assertSame('', $visitor->ip());
		$this->assertSame('', $visitor->userAgent());
		$this->assertNull($visitor->acceptedLanguage());
		$this->assertInstanceOf(Collection::class, $visitor->acceptedLanguages());
		$this->assertNull($visitor->acceptedMimeType());
		$this->assertInstanceOf(Collection::class, $visitor->acceptedMimeTypes());
	}

	public function testVisitorWithArguments(): void
	{
		$visitor = new Visitor([
			'ip'               => '192.168.1.1',
			'userAgent'        => 'Kirby',
			'acceptedLanguage' => 'en-US',
			'acceptedMimeType' => 'text/html'
		]);

		$this->assertSame('192.168.1.1', $visitor->ip());
		$this->assertSame('Kirby', $visitor->userAgent());
		$this->assertInstanceOf(Obj::class, $visitor->acceptedLanguage());
		$this->assertSame('en_US', $visitor->acceptedLanguage()->locale());
		$this->assertInstanceOf(Obj::class, $visitor->acceptedMimeType());
		$this->assertSame('text/html', $visitor->acceptedMimeType()->type());
	}

	public function testIp(): void
	{
		$visitor = new Visitor();
		$this->assertSame('', $visitor->ip());
		$this->assertInstanceOf(Visitor::class, $visitor->ip('192.168.1.1'));
		$this->assertSame('192.168.1.1', $visitor->ip());
	}

	public function testUserAgent(): void
	{
		$visitor = new Visitor();
		$this->assertInstanceOf(Visitor::class, $visitor->userAgent('Kirby'));
		$this->assertSame('Kirby', $visitor->userAgent());
	}

	public function testAcceptsMimeType(): void
	{
		$visitor = new Visitor();
		$this->assertFalse($visitor->acceptsMimeType('text/html'));

		$visitor = new Visitor(['acceptedMimeType' => 'text/html']);
		$this->assertTrue($visitor->acceptsMimeType('text/html'));
		$this->assertFalse($visitor->acceptsMimeType('application/json'));
	}

	public function testPreferredMimeType(): void
	{
		$visitor = new Visitor(['acceptedMimeType' => 'text/html;q=0.8,application/json,text/plain;q=0.9,text/*;q=0.7']);

		$this->assertSame('text/html', $visitor->preferredMimeType('text/html'));
		$this->assertSame('text/plain', $visitor->preferredMimeType('text/plain'));
		$this->assertSame('application/json', $visitor->preferredMimeType('application/json'));
		$this->assertSame('text/xml', $visitor->preferredMimeType('text/xml'));
		$this->assertNull($visitor->preferredMimeType('application/yaml'));

		$this->assertSame('text/plain', $visitor->preferredMimeType('text/html', 'text/plain'));
		$this->assertSame('text/plain', $visitor->preferredMimeType('text/plain', 'text/xml'));
		$this->assertSame('application/json', $visitor->preferredMimeType('text/html', 'application/json'));
		$this->assertSame('application/json', $visitor->preferredMimeType('text/plain', 'application/json'));
		$this->assertSame('application/json', $visitor->preferredMimeType('text/xml', 'application/json'));

		$this->assertSame('application/json', $visitor->preferredMimeType('text/html', 'text/plain', 'application/json'));
		$this->assertSame('application/json', $visitor->preferredMimeType('text/html', 'text/plain', 'application/json', 'text/xml'));

		$this->assertSame('application/json', $visitor->preferredMimeType('application/yaml', 'application/json'));
	}

	public function testPrefersJson(): void
	{
		$visitor = new Visitor(['acceptedMimeType' => 'text/html;q=0.8,application/json']);
		$this->assertTrue($visitor->prefersJson());

		$visitor = new Visitor(['acceptedMimeType' => 'application/json']);
		$this->assertTrue($visitor->prefersJson());

		$visitor = new Visitor(['acceptedMimeType' => 'text/html,application/json;q=0.8']);
		$this->assertFalse($visitor->prefersJson());

		$visitor = new Visitor(['acceptedMimeType' => 'text/html']);
		$this->assertFalse($visitor->prefersJson());

		$visitor = new Visitor(['acceptedMimeType' => 'text/xml']);
		$this->assertFalse($visitor->prefersJson());
	}

	public function testAcceptsLanguage(): void
	{
		$visitor = new Visitor(['acceptedLanguage' => 'en-US']);
		$this->assertTrue($visitor->acceptsLanguage('en_US'));
		$this->assertTrue($visitor->acceptsLanguage('en'));
		$this->assertFalse($visitor->acceptsLanguage('de_DE'));
		$this->assertFalse($visitor->acceptsLanguage('de'));
	}
}
