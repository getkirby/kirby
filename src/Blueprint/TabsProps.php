<?php

namespace Kirby\Blueprint;

class TabsProps
{
	public static function normalize(array $tabs): array
	{
		// unset / remove tab if its property is false
		$tabs = array_filter($tabs, fn ($tab) => $tab !== false);

		foreach ($tabs as $tabName => $tabProps) {
			$tabs[$tabName] = TabProps::normalize($tabName, $tabProps);
		}

		return $tabs;
	}
}
