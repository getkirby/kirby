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
