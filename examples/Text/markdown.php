<?php

require '../vendor/autoload.php';

use Kirby\Text\Markdown as Md;

// load some markdown
$text = file_get_contents('../README.md');

// simple
echo (new Md)->parse($text);

// enable markdown extra
echo (new Md(['extra' => true]))->parse($text);

// disable auto-breaks
echo (new Md(['breaks' => false]))->parse($text);
