<?php

namespace Kirby\Option;

use Kirby\Cms\Page;
use Kirby\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Options::class)]
class OptionsTest extends TestCase
{
	public function testConstruct()
	{
		$options = new Options([
			new Option('a'),
			new Option('b')
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('a', $options->first()->text->translations['en']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('b', $options->last()->text->translations['en']);
	}

	public function testFactory()
	{
		$options = Options::factory(['a', 'b']);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('a', $options->first()->text->translations['en']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('b', $options->last()->text->translations['en']);
	}

	public function testFactoryWithAssocArray()
	{
		$options = Options::factory([
			'a' => 'Option A',
			'b' => 'Option B'
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('Option A', $options->first()->text->translations['en']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('Option B', $options->last()->text->translations['en']);
	}

	public function testFactoryWithOptionArray()
	{
		$options = Options::factory([
			['value' => 'a', 'text' => 'Option A'],
			['value' => 'b', 'text' => 'Option B']
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('Option A', $options->first()->text->translations['en']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('Option B', $options->last()->text->translations['en']);
	}

	public function testFactoryWithTranslatedOptions()
	{
		$options = Options::factory([
			'a' => ['en' => 'Option A', 'de' => 'Variante A'],
			'b' => ['en' => 'Option B', 'de' => 'Variante B']
		]);

		$this->assertSame('a', $options->first()->value);
		$this->assertSame('Option A', $options->first()->text->translations['en']);
		$this->assertSame('Variante A', $options->first()->text->translations['de']);

		$this->assertSame('b', $options->last()->value);
		$this->assertSame('Option B', $options->last()->text->translations['en']);
		$this->assertSame('Variante B', $options->last()->text->translations['de']);
	}

	public function testRender()
	{
		$model = new Page(['slug' => 'test']);

		$options = new Options([
			new Option('a'),
			new Option('b')
		]);

		$this->assertSame([
			[
				'disabled' => false,
				'icon'     => null,
				'info'     => null,
				'text'     => 'a',
				'value'    => 'a',
			],
			[
				'disabled' => false,
				'icon'     => null,
				'info'     => null,
				'text'     => 'b',
				'value'    => 'b',
			]
		], $options->render($model));
	}
}
