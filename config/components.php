<?php

use Kirby\Cms\Response;
use Kirby\Cms\Snippet;
use Kirby\Cms\Template;
use Kirby\Text\SmartyPants;

return [
    'markdown' => function (string $text = null, array $options = []): string {
        static $markdown;

        if (isset($markdown) === false) {
            $parser   = ($options['extra'] ?? false) === true ? 'ParsedownExtra' : 'Parsedown';
            $markdown = new $parser;
            $markdown->setBreaksEnabled($options['breaks'] ?? true);
        }

        // we need the @ here, because parsedown has some notice issues :(
        return @$markdown->text($text);
    },
    'response' => function ($input) {
        return Response::for($input);
    },
    'smartypants' => function (string $text = null, array $options = []): string {
        static $smartypants;

        $smartypants = $smartypants ?? new Smartypants($options);

        return $smartypants->parse($text);
    },
    'snippet' => function (string $name, array $data = []) {
        return new Snippet($name, $data);
    },
    'template' => function (string $name, array $data = [], string $appendix = null) {
        return new Template($name, $data, $appendix);
    }
];
