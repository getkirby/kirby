<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Core
 */
class CoreTest extends TestCase
{
    protected $core;

    public function setUp(): void
    {
        parent::setUp();
        $this->core = new Core($this->app);
    }

    /**
     * @covers ::area
     */
    public function testArea()
    {
        $area = $this->core->area('site');

        $this->assertSame('Site', $area['label']);
    }

    /**
     * @covers ::areas
     */
    public function testAreas()
    {
        $areas = $this->core->areas();
        $this->assertArrayHasKey('account', $areas);
        $this->assertArrayHasKey('installation', $areas);
        $this->assertArrayHasKey('login', $areas);
        $this->assertArrayHasKey('system', $areas);
        $this->assertArrayHasKey('site', $areas);
        $this->assertArrayHasKey('users', $areas);
    }

    /**
     * @covers ::authChallenges
     */
    public function testAuthChallenges()
    {
        $authChallenges = $this->core->authChallenges();
        $this->assertArrayHasKey('email', $authChallenges);
    }

    /**
     * @covers ::blueprintPresets
     */
    public function testBlueprintPresets()
    {
        $blueprintPresets = $this->core->blueprintPresets();
        $this->assertArrayHasKey('pages', $blueprintPresets);
        $this->assertArrayHasKey('page', $blueprintPresets);
        $this->assertArrayHasKey('files', $blueprintPresets);
    }

    /**
     * @covers ::blueprints
     */
    public function testBlueprints()
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

    /**
     * @covers ::cacheTypes
     */
    public function testCacheTypes()
    {
        $cacheTypes = $this->core->cacheTypes();

        $this->assertArrayHasKey('apcu', $cacheTypes);
        $this->assertArrayHasKey('file', $cacheTypes);
        $this->assertArrayHasKey('memcached', $cacheTypes);
        $this->assertArrayHasKey('memory', $cacheTypes);
    }

    /**
     * @covers ::components
     */
    public function testComponents()
    {
        $components = $this->core->components();

        $this->assertArrayHasKey('css', $components);
        $this->assertArrayHasKey('dump', $components);
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

    /**
     * @covers ::fieldMethodAliases
     */
    public function testFieldMethodAliases()
    {
        $aliases = $this->core->fieldMethodAliases();

        $this->assertSame('toBool', $aliases['bool']);
        $this->assertSame('escape', $aliases['esc']);
        $this->assertSame('toExcerpt', $aliases['excerpt']);
        $this->assertSame('toFloat', $aliases['float']);
        $this->assertSame('html', $aliases['h']);
        $this->assertSame('toInt', $aliases['int']);
        $this->assertSame('kirbytext', $aliases['kt']);
        $this->assertSame('kirbytextinline', $aliases['kti']);
        $this->assertSame('toLink', $aliases['link']);
        $this->assertSame('markdown', $aliases['md']);
        $this->assertSame('smartypants', $aliases['sp']);
        $this->assertSame('isValid', $aliases['v']);
        $this->assertSame('xml', $aliases['x']);
    }

    /**
     * @covers ::fieldMethods
     */
    public function testFieldMethods()
    {
        $methods = $this->core->fieldMethods();

        $this->assertArrayHasKey('isFalse', $methods);
        $this->assertArrayHasKey('isTrue', $methods);
        $this->assertArrayHasKey('isValid', $methods);
        $this->assertArrayHasKey('toBlocks', $methods);
        $this->assertArrayHasKey('toBool', $methods);
        $this->assertArrayHasKey('toData', $methods);
        $this->assertArrayHasKey('toDate', $methods);
        $this->assertArrayHasKey('toFile', $methods);
        $this->assertArrayHasKey('toFiles', $methods);
        $this->assertArrayHasKey('toFloat', $methods);
        $this->assertArrayHasKey('toInt', $methods);
        $this->assertArrayHasKey('toLayouts', $methods);
        $this->assertArrayHasKey('toLink', $methods);
        $this->assertArrayHasKey('toPage', $methods);
        $this->assertArrayHasKey('toPages', $methods);
        $this->assertArrayHasKey('toPages', $methods);
        $this->assertArrayHasKey('toStructure', $methods);
        $this->assertArrayHasKey('toTimestamp', $methods);
        $this->assertArrayHasKey('toUrl', $methods);
        $this->assertArrayHasKey('toUser', $methods);
        $this->assertArrayHasKey('toUsers', $methods);
        $this->assertArrayHasKey('length', $methods);
        $this->assertArrayHasKey('words', $methods);
        $this->assertArrayHasKey('words', $methods);
        $this->assertArrayHasKey('callback', $methods);
        $this->assertArrayHasKey('escape', $methods);
        $this->assertArrayHasKey('excerpt', $methods);
        $this->assertArrayHasKey('html', $methods);
        $this->assertArrayHasKey('inline', $methods);
        $this->assertArrayHasKey('kirbytext', $methods);
        $this->assertArrayHasKey('kirbytextinline', $methods);
        $this->assertArrayHasKey('kirbytags', $methods);
        $this->assertArrayHasKey('lower', $methods);
        $this->assertArrayHasKey('markdown', $methods);
        $this->assertArrayHasKey('nl2br', $methods);
        $this->assertArrayHasKey('query', $methods);
        $this->assertArrayHasKey('replace', $methods);
        $this->assertArrayHasKey('short', $methods);
        $this->assertArrayHasKey('slug', $methods);
        $this->assertArrayHasKey('smartypants', $methods);
        $this->assertArrayHasKey('split', $methods);
        $this->assertArrayHasKey('upper', $methods);
        $this->assertArrayHasKey('widont', $methods);
        $this->assertArrayHasKey('xml', $methods);
        $this->assertArrayHasKey('yaml', $methods);
    }

    /**
     * @covers ::fieldMixins
     */
    public function testFieldMixins()
    {
        $mixins = $this->core->fieldMixins();

        $this->assertArrayHasKey('datetime', $mixins);
        $this->assertArrayHasKey('filepicker', $mixins);
        $this->assertArrayHasKey('min', $mixins);
        $this->assertArrayHasKey('layout', $mixins);
        $this->assertArrayHasKey('options', $mixins);
        $this->assertArrayHasKey('pagepicker', $mixins);
        $this->assertArrayHasKey('picker', $mixins);
        $this->assertArrayHasKey('upload', $mixins);
        $this->assertArrayHasKey('userpicker', $mixins);
    }

    /**
     * @covers ::fields
     */
    public function testFields()
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

    /**
     * @covers ::load
     */
    public function testLoad()
    {
        $loader = $this->core->load();

        $this->assertInstanceOf('Kirby\Cms\Loader', $loader);
        $this->assertFalse($loader->withPlugins());
    }

    /**
     * @covers ::roots
     */
    public function testRoots()
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

    /**
     * @covers ::routes
     */
    public function testRoutes()
    {
        $routes = $this->core->routes();

        $this->assertArrayHasKey('before', $routes);
        $this->assertArrayHasKey('after', $routes);
    }

    /**
     * @covers ::snippets
     */
    public function testSnippets()
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

    /**
     * @covers ::kirbyTagAliases
     */
    public function testKirbyTagAliases()
    {
        $aliases = $this->core->kirbyTagAliases();

        $this->assertArrayHasKey('youtube', $aliases);
        $this->assertArrayHasKey('vimeo', $aliases);
    }

    /**
     * @covers ::kirbyTags
     */
    public function testKirbyTags()
    {
        $tags = $this->core->kirbyTags();

        $this->assertArrayHasKey('date', $tags);
        $this->assertArrayHasKey('email', $tags);
        $this->assertArrayHasKey('file', $tags);
        $this->assertArrayHasKey('gist', $tags);
        $this->assertArrayHasKey('image', $tags);
        $this->assertArrayHasKey('link', $tags);
        $this->assertArrayHasKey('tel', $tags);
        $this->assertArrayHasKey('twitter', $tags);
        $this->assertArrayHasKey('video', $tags);
    }

    /**
     * @covers ::sectionMixins
     */
    public function testSectionMixins()
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

    /**
     * @covers ::sections
     */
    public function testSections()
    {
        $sections = $this->core->sections();

        $this->assertArrayHasKey('fields', $sections);
        $this->assertArrayHasKey('files', $sections);
        $this->assertArrayHasKey('info', $sections);
        $this->assertArrayHasKey('pages', $sections);
    }

    /**
     * @covers ::templates
     */
    public function testTemplates()
    {
        $templates = $this->core->templates();

        $this->assertArrayHasKey('emails/auth/login', $templates);
        $this->assertArrayHasKey('emails/auth/password-reset', $templates);
    }

    /**
     * @covers ::urls
     */
    public function testUrls()
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
