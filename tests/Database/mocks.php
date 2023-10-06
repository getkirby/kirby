<?php

namespace Kirby\Database;

class MockSql extends Sql
{
	public function columns(string $table): array
	{
	}

	public function tables(): array
	{
	}
}

class MockClassWithCallable
{
	public function __construct(
		public string $fname,
		public string $lname
	) {
	}

	public function name(): string
	{
		return $this->fname . ' ' . $this->lname;
	}

	public static function fromDb(array $row, $key = null): MockClassWithCallable
	{
		return new MockClassWithCallable($row['fname'], $row['lname']);
	}
}
