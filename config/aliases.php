<?php

return [
	// cms classes
	'collection' => 'Kirby\Cms\Collection',
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

	// content classes
	'field'      => 'Kirby\Content\Field',

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

	// image classes
	'dimensions' => 'Kirby\Image\Dimensions',

	// panel classes
	'panel'      => 'Kirby\Panel\Panel',

	// template classes
	'snippet'    => 'Kirby\Template\Snippet',
	'slot'       => 'Kirby\Template\Slot',

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

	// Deprecated aliases:
	// Any of these might be removed at any point in the future
	'kirby\cms\asset'              => 'Kirby\Filesystem\Asset',
	'kirby\cms\content'            => 'Kirby\Content\Content',
	'kirby\cms\dir'                => 'Kirby\Filesystem\Dir',
	'kirby\cms\filename'           => 'Kirby\Filesystem\Filename',
	'kirby\cms\filefoundation'     => 'Kirby\Filesystem\IsFile',
	'kirby\cms\field'              => 'Kirby\Content\Field',
	'kirby\cms\form'               => 'Kirby\Form\Form',
	'kirby\cms\kirbytag'           => 'Kirby\Text\KirbyTag',
	'kirby\cms\kirbytags'          => 'Kirby\Text\KirbyTags',
	'kirby\cms\plugin'             => 'Kirby\Plugin\Plugin',
	'kirby\cms\pluginasset'        => 'Kirby\Plugin\Asset',
	'kirby\cms\pluginassets'       => 'Kirby\Plugin\Assets',
	'kirby\cms\template'           => 'Kirby\Template\Template',
	'kirby\form\options'           => 'Kirby\Option\Options',
	'kirby\form\optionsapi'        => 'Kirby\Option\OptionsApi',
	'kirby\form\optionsquery'      => 'Kirby\Option\OptionsQuery',
	'kirby\toolkit\dir'            => 'Kirby\Filesystem\Dir',
	'kirby\toolkit\f'              => 'Kirby\Filesystem\F',
	'kirby\toolkit\file'           => 'Kirby\Filesystem\File',
	'kirby\toolkit\mime'           => 'Kirby\Filesystem\Mime',
	'kirby\toolkit\query'          => 'Kirby\Query\Query',

	'kirby\panel\ui\buttons\languagecreatebutton' => 'Kirby\Panel\Ui\Button\LanguageCreateButton',
	'kirby\panel\ui\buttons\languagedeletebutton' => 'Kirby\Panel\Ui\Button\LanguageDeleteButton',
	'kirby\panel\ui\buttons\languagesettingsbutton' => 'Kirby\Panel\Ui\Button\LanguageSettingsButton',
	'kirby\panel\ui\buttons\openbutton' => 'Kirby\Panel\Ui\Button\OpenButton',
	'kirby\panel\ui\buttons\pagestatusbutton' => 'Kirby\Panel\Ui\Button\PageStatusButton',
	'kirby\panel\ui\buttons\previewbutton' => 'Kirby\Panel\Ui\Button\PreviewButton',
	'kirby\panel\ui\buttons\versionsbutton' => 'Kirby\Panel\Ui\Button\VersionsButton',
	'kirby\panel\ui\buttons\viewbutton' => 'Kirby\Panel\Ui\Button\ViewButton',
	'kirby\panel\ui\buttons\viewbuttons' => 'Kirby\Panel\Ui\Button\ViewButtons',
	'kirby\panel\ui\filewpreviews\audiofilepreview' => 'Kirby\Panel\Ui\FilePreview\AudioFilePreview',
	'kirby\panel\ui\filewpreviews\defaultfilepreview' => 'Kirby\Panel\Ui\FilePreview\DefaultFilePreview',
	'kirby\panel\ui\filewpreviews\imagefilepreview' => 'Kirby\Panel\Ui\FilePreview\ImageFilePreview',
	'kirby\panel\ui\filewpreviews\pdfpreview' => 'Kirby\Panel\Ui\FilePreview\PdfPreview',
	'kirby\panel\ui\filewpreviews\videofilepreview' => 'Kirby\Panel\Ui\FilePreview\VideoFilePreview',
];
