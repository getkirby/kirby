<?php

namespace Kirby\Filesystem;

use Kirby\Cms\Page;

class DirBench
{
	public function benchInventoryNoModels()
	{
		Dir::inventory(__DIR__ . '/fixtures/inventory/models');
	}

	public function benchInventoryOneModels()
	{
		Page::$models = [
			'a' => 'A'
		];

		Dir::inventory(__DIR__ . '/fixtures/inventory/models');

		Page::$models = [];
	}

	public function benchInventoryTwoModels()
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'B'
		];

		Dir::inventory(__DIR__ . '/fixtures/inventory/models');

		Page::$models = [];
	}

	public function benchInventoryFiveModels()
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
			'd' => 'D',
			'e' => 'E'
		];

		Dir::inventory(__DIR__ . '/fixtures/inventory/models');

		Page::$models = [];
	}

	public function benchInventory26Models()
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
			'd' => 'D',
			'e' => 'E',
			'f' => 'F',
			'g' => 'G',
			'h' => 'H',
			'i' => 'I',
			'j' => 'J',
			'k' => 'K',
			'l' => 'L',
			'm' => 'M',
			'n' => 'N',
			'o' => 'O',
			'p' => 'P',
			'q' => 'Q',
			'r' => 'R',
			's' => 'S',
			't' => 'T',
			'u' => 'U',
			'v' => 'V',
			'w' => 'W',
			'x' => 'X',
			'y' => 'Y',
			'z' => 'Z'
		];

		Dir::inventory(__DIR__ . '/fixtures/inventory/models');

		Page::$models = [];
	}
}
