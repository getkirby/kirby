<?php

use Kirby\Cms\App;
use Kirby\Cms\Response;
use Kirby\Cms\Snippet;
use Kirby\Cms\Template;
use Kirby\Text\SmartyPants;

return [
    'markdown' => function (App $kirby, string $text = null, array $options = []): string {
        static $markdown;

        if (isset($markdown) === false) {
            $parser   = ($options['extra'] ?? false) === true ? 'ParsedownExtra' : 'Parsedown';
            $markdown = new $parser;
            $markdown->setBreaksEnabled($options['breaks'] ?? true);
        }

        // we need the @ here, because parsedown has some notice issues :(
        return @$markdown->text($text);
    },
    'response' => function (App $kirby, $input) {
        return Response::for($input);
    },
    'smartypants' => function (App $kirby, string $text = null, array $options = []): string {
        static $smartypants;

        $smartypants = $smartypants ?? new Smartypants($options);

        return $smartypants->parse($text);
    },
    'snippet' => function (App $kirby, string $name, array $data = []) {
        return new Snippet($name, $data);
    },
    'template' => function (App $kirby, string $name, array $data = [], string $appendix = null) {
        return new Template($name, $data, $appendix);
    }
];
