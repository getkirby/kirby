<?php

require '../vendor/autoload.php';

$text = <<<TEXT
    This is (link: https://getkirby.com text: Kirby's) custom text parser.

    It supports a variety of custom tags which are easy to understand and write. It's meant to be an addition to Markdown.

    (image: https://unsplash.it/200/300 link: https://unspash.com )

    Last Update: (date: d.m.Y)

    (email: support@getkirby.com)

    (tel: +49 1234 5678)

    (file: text.txt)

    (twitter: getkirby)

    (vimeo: https://vimeo.com/209467146)

    (youtube: https://www.youtube.com/watch?v=j-EDvqAjvr0)

    (gist: https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144)
TEXT;

// load the file and add breaks for a bit more "style"
echo (new Kirby\Text\Tags)->parse(nl2br($text));
