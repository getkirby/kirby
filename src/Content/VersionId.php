<?php

namespace Kirby\Content;

enum VersionId: string
{
	case PUBLISHED = 'published';
	case CHANGES   = 'changes';
}
