<?php

namespace Kirby\Permissions;

use Kirby\Cms\Language;

class LanguagePermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}

	public static function for(Language $language): static
	{
		return match (true) {
			$language->isSingle()  => static::forSingleLanguage(),
			$language->isDefault() => static::forDefaultLanguage($language),
			default                => new static()
		};
	}

	public static function forDefaultLanguage(Language $language): static
	{
		if ($language->isLast() === false) {
			return new static(
				delete: false
			);
		}

		return new static();
	}

	public static function forSingleLanguage(): static
	{
		return new static(
			create: false,
			delete: false
		);
	}
}
