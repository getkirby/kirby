<?php

return [
    // cms classes
    'collection' => 'Kirby\Cms\Collection',
    'field'      => 'Kirby\Cms\Field',
    'file'       => 'Kirby\Cms\File',
    'files'      => 'Kirby\Cms\Files',
    'find'       => 'Kirby\Cms\Find',
    'helpers'    => 'Kirby\Cms\Helpers',
    'html'       => 'Kirby\Cms\Html',
    'kirby'      => 'Kirby\Cms\App',
    'page'       => 'Kirby\Cms\Page',
    'pages'      => 'Kirby\Cms\Pages',
    'pagination' => 'Kirby\Cms\Pagination',
    'r'          => 'Kirby\Cms\R',
    'response'   => 'Kirby\Cms\Response',
    's'          => 'Kirby\Cms\S',
    'sane'       => 'Kirby\Sane\Sane',
    'site'       => 'Kirby\Cms\Site',
    'structure'  => 'Kirby\Cms\Structure',
    'url'        => 'Kirby\Cms\Url',
    'user'       => 'Kirby\Cms\User',
    'users'      => 'Kirby\Cms\Users',
    'visitor'    => 'Kirby\Cms\Visitor',

    // data handler
    'data'      => 'Kirby\Data\Data',
    'json'      => 'Kirby\Data\Json',
    'yaml'      => 'Kirby\Data\Yaml',

    // file classes
    'asset'      => 'Kirby\Filesystem\Asset',
    'dir'        => 'Kirby\Filesystem\Dir',
    'f'          => 'Kirby\Filesystem\F',
    'mime'       => 'Kirby\Filesystem\Mime',

    // data classes
    'database'  => 'Kirby\Database\Database',
    'db'        => 'Kirby\Database\Db',

    // exceptions
    'errorpageexception' => 'Kirby\Exception\ErrorPageException',

    // http classes
    'cookie'     => 'Kirby\Http\Cookie',
    'header'     => 'Kirby\Http\Header',
    'remote'     => 'Kirby\Http\Remote',
    'server'     => 'Kirby\Http\Server',

    // image classes
    'dimensions' => 'Kirby\Image\Dimensions',

    // panel classes
    'panel'      => 'Kirby\Panel\Panel',

    // toolkit classes
    'a'          => 'Kirby\Toolkit\A',
    'c'          => 'Kirby\Toolkit\Config',
    'config'     => 'Kirby\Toolkit\Config',
    'escape'     => 'Kirby\Toolkit\Escape',
    'i18n'       => 'Kirby\Toolkit\I18n',
    'obj'        => 'Kirby\Toolkit\Obj',
    'str'        => 'Kirby\Toolkit\Str',
    'tpl'        => 'Kirby\Toolkit\Tpl',
    'v'          => 'Kirby\Toolkit\V',
    'xml'        => 'Kirby\Toolkit\Xml',

    // TODO: remove in 4.0.0
    'kirby\cms\asset'          => 'Kirby\Filesystem\Asset',
    'kirby\cms\dir'            => 'Kirby\Filesystem\Dir',
    'kirby\cms\filename'       => 'Kirby\Filesystem\Filename',
    'kirby\cms\filefoundation' => 'Kirby\Filesystem\IsFile',
    'kirby\cms\form'           => 'Kirby\Form\Form',
    'kirby\cms\kirbytag'       => 'Kirby\Text\KirbyTag',
    'kirby\cms\kirbytags'      => 'Kirby\Text\KirbyTags',
    'kirby\toolkit\dir'        => 'Kirby\Filesystem\Dir',
    'kirby\toolkit\f'          => 'Kirby\Filesystem\F',
    'kirby\toolkit\file'       => 'Kirby\Filesystem\File',
    'kirby\toolkit\mime'       => 'Kirby\Filesystem\Mime',
];
