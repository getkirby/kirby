<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Core::class)]
class CoreTest extends TestCase
{
	protected Core $core;

	public function setUp(): void
	{
		parent::setUp();
		$this->core = new Core($this->app);
	}

	public function testArea(): void
	{
		$area = $this->core->area('site');

		$this->assertSame('Site', $area['label']);
	}

	public function testAreas(): void
	{
		$areas = $this->core->areas();
		$this->assertArrayHasKey('account', $areas);
		$this->assertArrayHasKey('installation', $areas);
		$this->assertArrayHasKey('login', $areas);
		$this->assertArrayHasKey('system', $areas);
		$this->assertArrayHasKey('site', $areas);
		$this->assertArrayHasKey('users', $areas);
	}

	public function testAuthChallenges(): void
	{
		$authChallenges = $this->core->authChallenges();
		$this->assertArrayHasKey('email', $authChallenges);
	}

	public function testBlueprintPresets(): void
	{
		$blueprintPresets = $this->core->blueprintPresets();
		$this->assertArrayHasKey('pages', $blueprintPresets);
		$this->assertArrayHasKey('page', $blueprintPresets);
		$this->assertArrayHasKey('files', $blueprintPresets);
	}

	public function testBlueprints(): void
	{
		$blueprints = $this->core->blueprints();

		$this->assertArrayHasKey('blocks/code', $blueprints);
		$this->assertArrayHasKey('blocks/gallery', $blueprints);
		$this->assertArrayHasKey('blocks/heading', $blueprints);
		$this->assertArrayHasKey('blocks/image', $blueprints);
		$this->assertArrayHasKey('blocks/line', $blueprints);
		$this->assertArrayHasKey('blocks/list', $blueprints);
		$this->assertArrayHasKey('blocks/markdown', $blueprints);
		$this->assertArrayHasKey('blocks/quote', $blueprints);
		$this->assertArrayHasKey('blocks/table', $blueprints);
		$this->assertArrayHasKey('blocks/text', $blueprints);
		$this->assertArrayHasKey('blocks/video', $blueprints);

		$this->assertArrayHasKey('files/default', $blueprints);
		$this->assertArrayHasKey('pages/default', $blueprints);
		$this->assertArrayHasKey('site', $blueprints);
	}

	public function testCaches(): void
	{
		$caches = $this->core->caches();

		$this->assertArrayHasKey('updates', $caches);
		$this->assertArrayHasKey('uuid', $caches);
	}

	public function testCacheTypes(): void
	{
		$cacheTypes = $this->core->cacheTypes();

		$this->assertArrayHasKey('apcu', $cacheTypes);
		$this->assertArrayHasKey('file', $cacheTypes);
		$this->assertArrayHasKey('memcached', $cacheTypes);
		$this->assertArrayHasKey('memory', $cacheTypes);
	}

	public function testComponents(): void
	{
		$components = $this->core->components();

		$this->assertArrayHasKey('css', $components);
		$this->assertArrayHasKey('file::url', $components);
		$this->assertArrayHasKey('file::version', $components);
		$this->assertArrayHasKey('js', $components);
		$this->assertArrayHasKey('markdown', $components);
		$this->assertArrayHasKey('search', $components);
		$this->assertArrayHasKey('smartypants', $components);
		$this->assertArrayHasKey('snippet', $components);
		$this->assertArrayHasKey('template', $components);
		$this->assertArrayHasKey('thumb', $components);
		$this->assertArrayHasKey('url', $components);
	}

	public function testFieldMixins(): void
	{
		$mixins = $this->core->fieldMixins();

		$this->assertArrayHasKey('datetime', $mixins);
		$this->assertArrayHasKey('min', $mixins);
		$this->assertArrayHasKey('layout', $mixins);
		$this->assertArrayHasKey('options', $mixins);
		$this->assertArrayHasKey('picker', $mixins);
		$this->assertArrayHasKey('upload', $mixins);
	}

	public function testFields(): void
	{
		$fields = $this->core->fields();

		$this->assertArrayHasKey('blocks', $fields);
		$this->assertArrayHasKey('checkboxes', $fields);
		$this->assertArrayHasKey('date', $fields);
		$this->assertArrayHasKey('email', $fields);
		$this->assertArrayHasKey('files', $fields);
		$this->assertArrayHasKey('gap', $fields);
		$this->assertArrayHasKey('headline', $fields);
		$this->assertArrayHasKey('hidden', $fields);
		$this->assertArrayHasKey('info', $fields);
		$this->assertArrayHasKey('layout', $fields);
		$this->assertArrayHasKey('line', $fields);
		$this->assertArrayHasKey('list', $fields);
		$this->assertArrayHasKey('multiselect', $fields);
		$this->assertArrayHasKey('number', $fields);
		$this->assertArrayHasKey('pages', $fields);
		$this->assertArrayHasKey('radio', $fields);
		$this->assertArrayHasKey('range', $fields);
		$this->assertArrayHasKey('select', $fields);
		$this->assertArrayHasKey('slug', $fields);
		$this->assertArrayHasKey('structure', $fields);
		$this->assertArrayHasKey('tags', $fields);
		$this->assertArrayHasKey('tel', $fields);
		$this->assertArrayHasKey('text', $fields);
		$this->assertArrayHasKey('textarea', $fields);
		$this->assertArrayHasKey('time', $fields);
		$this->assertArrayHasKey('toggle', $fields);
		$this->assertArrayHasKey('url', $fields);
		$this->assertArrayHasKey('users', $fields);
		$this->assertArrayHasKey('writer', $fields);
	}

	public function testFilePreviews(): void
	{
		$previews = $this->core->filePreviews();
		$this->assertCount(4, $previews);
	}

	public function testLoad(): void
	{
		$loader = $this->core->load();

		$this->assertInstanceOf(Loader::class, $loader);
		$this->assertFalse($loader->withPlugins());
	}

	public function testRoots(): void
	{
		$roots = $this->core->roots();

		$this->assertArrayHasKey('kirby', $roots);
		$this->assertArrayHasKey('i18n', $roots);
		$this->assertArrayHasKey('i18n:translations', $roots);
		$this->assertArrayHasKey('i18n:rules', $roots);
		$this->assertArrayHasKey('index', $roots);
		$this->assertArrayHasKey('assets', $roots);
		$this->assertArrayHasKey('content', $roots);
		$this->assertArrayHasKey('media', $roots);
		$this->assertArrayHasKey('panel', $roots);
		$this->assertArrayHasKey('site', $roots);
		$this->assertArrayHasKey('accounts', $roots);
		$this->assertArrayHasKey('blueprints', $roots);
		$this->assertArrayHasKey('cache', $roots);
		$this->assertArrayHasKey('collections', $roots);
		$this->assertArrayHasKey('commands', $roots);
		$this->assertArrayHasKey('config', $roots);
		$this->assertArrayHasKey('controllers', $roots);
		$this->assertArrayHasKey('languages', $roots);
		$this->assertArrayHasKey('license', $roots);
		$this->assertArrayHasKey('logs', $roots);
		$this->assertArrayHasKey('models', $roots);
		$this->assertArrayHasKey('plugins', $roots);
		$this->assertArrayHasKey('sessions', $roots);
		$this->assertArrayHasKey('snippets', $roots);
		$this->assertArrayHasKey('templates', $roots);
		$this->assertArrayHasKey('roles', $roots);
	}

	public function testRoutes(): void
	{
		$routes = $this->core->routes();

		$this->assertArrayHasKey('before', $routes);
		$this->assertArrayHasKey('after', $routes);
	}

	public function testSnippets(): void
	{
		$snippets = $this->core->snippets();

		$this->assertArrayHasKey('blocks/code', $snippets);
		$this->assertArrayHasKey('blocks/gallery', $snippets);
		$this->assertArrayHasKey('blocks/heading', $snippets);
		$this->assertArrayHasKey('blocks/image', $snippets);
		$this->assertArrayHasKey('blocks/line', $snippets);
		$this->assertArrayHasKey('blocks/list', $snippets);
		$this->assertArrayHasKey('blocks/markdown', $snippets);
		$this->assertArrayHasKey('blocks/quote', $snippets);
		$this->assertArrayHasKey('blocks/table', $snippets);
		$this->assertArrayHasKey('blocks/text', $snippets);
		$this->assertArrayHasKey('blocks/video', $snippets);
	}

	public function testKirbyTagAliases(): void
	{
		$aliases = $this->core->kirbyTagAliases();

		$this->assertArrayHasKey('youtube', $aliases);
		$this->assertArrayHasKey('vimeo', $aliases);
	}

	public function testKirbyTags(): void
	{
		$tags = $this->core->kirbyTags();

		$this->assertArrayHasKey('date', $tags);
		$this->assertArrayHasKey('email', $tags);
		$this->assertArrayHasKey('file', $tags);
		$this->assertArrayHasKey('gist', $tags);
		$this->assertArrayHasKey('image', $tags);
		$this->assertArrayHasKey('link', $tags);
		$this->assertArrayHasKey('tel', $tags);
		$this->assertArrayHasKey('video', $tags);
	}

	public function testSectionMixins(): void
	{
		$mixins = $this->core->sectionMixins();

		$this->assertArrayHasKey('empty', $mixins);
		$this->assertArrayHasKey('headline', $mixins);
		$this->assertArrayHasKey('help', $mixins);
		$this->assertArrayHasKey('layout', $mixins);
		$this->assertArrayHasKey('max', $mixins);
		$this->assertArrayHasKey('min', $mixins);
		$this->assertArrayHasKey('pagination', $mixins);
		$this->assertArrayHasKey('parent', $mixins);
	}

	public function testSections(): void
	{
		$sections = $this->core->sections();

		$this->assertArrayHasKey('fields', $sections);
		$this->assertArrayHasKey('files', $sections);
		$this->assertArrayHasKey('info', $sections);
		$this->assertArrayHasKey('pages', $sections);
	}

	public function testTemplates(): void
	{
		$templates = $this->core->templates();

		$this->assertArrayHasKey('emails/auth/login', $templates);
		$this->assertArrayHasKey('emails/auth/password-reset', $templates);
	}

	public function testUrls(): void
	{
		$urls = $this->core->urls();

		$this->assertArrayHasKey('index', $urls);
		$this->assertArrayHasKey('base', $urls);
		$this->assertArrayHasKey('current', $urls);
		$this->assertArrayHasKey('assets', $urls);
		$this->assertArrayHasKey('api', $urls);
		$this->assertArrayHasKey('media', $urls);
		$this->assertArrayHasKey('panel', $urls);
	}
}
