<?php

$blocksRoot = dirname(__DIR__) . '/blocks';

return [
    // blocks
    'blocks/code'    => $blocksRoot . '/code/code.yml',
    'blocks/heading' => $blocksRoot . '/heading/heading.yml',
    'blocks/image'   => $blocksRoot . '/image/image.yml',
    'blocks/quote'   => $blocksRoot . '/quote/quote.yml',
    'blocks/table'   => $blocksRoot . '/table/table.yml',
    'blocks/text'    => $blocksRoot . '/text/text.yml',
    'blocks/video'   => $blocksRoot . '/video/video.yml',

    // file blueprints
    'files/default' => __DIR__ . '/blueprints/files/default.yml',

    // page blueprints
    'pages/default' => __DIR__ . '/blueprints/pages/default.yml',

    // site blueprints
    'site' => __DIR__ . '/blueprints/site.yml'
];
