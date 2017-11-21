<?php

require '../vendor/autoload.php';

$text = 'Text with "nasty" quotes';

echo (new Kirby\Text\SmartyPants)->parse($text);
