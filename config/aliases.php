<?php

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Structure;
use Kirby\Cms\Form;
use Kirby\Cms\Response;

use Kirby\Data\Handler\Yaml;
use Kirby\Util\A;
use Kirby\Util\Dir;
use Kirby\Util\F;
use Kirby\Util\I18n;
use Kirby\Util\Str;
use Kirby\Toolkit\V;

class_alias(A::class, 'A');
class_alias(App::class, 'Kirby');
class_alias(Dir::class, 'Dir');
class_alias(F::class, 'F');
class_alias(File::class, 'File');
//class_alias(Form::class, 'Form');
class_alias(I18n::class, 'I18n');
class_alias(Page::class, 'Page');
class_alias(Site::class, 'Site');
class_alias(Response::class, 'Response');
class_alias(Structure::class, 'Structure');
class_alias(Str::class, 'Str');
class_alias(V::class, 'V');
class_alias(Yaml::class, 'Yaml');
