<?php

namespace Kirby\Model;

enum PageStatus: string
{
	case Draft = 'draft';
	case Listed = 'listed';
	case Unlisted = 'unlisted';
}
