<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

enum LanguageStatus: string {
	case DRAFT  = 'draft';
	case PUBLIC = 'public';

	public function label(): string
	{
		return match ($this) {
			LanguageStatus::DRAFT  => I18n::translate('draft'),
			LanguageStatus::PUBLIC => I18n::translate('public')
		};
	}

	public function value(): string
	{
		return $this->value;
	}
}
