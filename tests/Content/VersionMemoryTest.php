<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionMemory::class)]
class VersionMemoryTest extends TestCase
{
	public function language(string $code = 'current'): Language
	{
		return Language::ensure($code);
	}

	public function version(string $id = 'latest'): Version
	{
		return new Version(
			model: new Page(['slug' => 'test']),
			id: VersionId::from($id)
		);
	}

	public function testLanguages()
	{
		$this->setUpMultiLanguage();

		$memoryEN = new VersionMemory(
			version: $this->version(),
			language: $this->language('en')
		);

		$memoryDE = new VersionMemory(
			version: $this->version(),
			language: $this->language('de')
		);

		$this->assertSame([], $memoryEN->read());
		$this->assertSame([], $memoryDE->read());

		$memoryEN->set('foo', 'bar');
		$memoryDE->set('foo', 'baz');

		$this->assertSame(['foo' => 'bar'], $memoryEN->read());
		$this->assertSame(['foo' => 'baz'], $memoryDE->read());

		$memoryEN->flush();
		$memoryDE->flush();

		$this->assertSame([], $memoryEN->read());
		$this->assertSame([], $memoryDE->read());
	}

	public function testReadWriteAndFlush()
	{
		$memory = new VersionMemory(
			version: $this->version(),
			language: $this->language()
		);

		$this->assertSame([], $memory->read());

		$memory->write(['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $memory->read());

		$memory->flush();

		$this->assertSame([], $memory->read());
	}

	public function testSetGetAndRemove()
	{
		$memory = new VersionMemory(
			version: $this->version(),
			language: $this->language()
		);

		$this->assertSame(null, $memory->get('foo'));
		$this->assertSame('bar', $memory->get('foo', 'bar'));

		$memory->set('foo', 'bar');

		$this->assertSame('bar', $memory->get('foo'));

		$memory->remove('foo');

		$this->assertSame(null, $memory->get('foo'));
	}

	public function testUpdate()
	{
		$memory = new VersionMemory(
			version: $this->version(),
			language: $this->language()
		);

		$this->assertSame([], $memory->read());

		$memory->update(['a' => 'A']);
		$memory->update(['b' => 'B']);

		$this->assertSame([
			'a' => 'A',
			'b' => 'B'
		], $memory->read());
	}

	public function testVersions()
	{
		$memoryLatest = new VersionMemory(
			version: $this->version('latest'),
			language: $this->language()
		);

		$memoryChanges = new VersionMemory(
			version: $this->version('changes'),
			language: $this->language()
		);

		$memoryLatest->set('foo', 'bar');
		$memoryChanges->set('foo', 'baz');

		$this->assertSame(['foo' => 'bar'], $memoryLatest->read());
		$this->assertSame(['foo' => 'baz'], $memoryChanges->read());

		$memoryLatest->flush();
		$memoryChanges->flush();

		$this->assertSame([], $memoryLatest->read());
		$this->assertSame([], $memoryChanges->read());
	}
}
