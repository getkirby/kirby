<?php

namespace Kirby\Cms;

class ExtendedModelWithContent extends ModelWithContent
{
    public function blueprint()
    {
        return 'test';
    }

    protected function commit(string $action, array $arguments, \Closure $callback)
    {
        // nothing to commit in the test
    }

    public function contentFileName(): string
    {
        return 'test.txt';
    }

    public function panel()
    {
        return new PageForPanel($this);
    }

    public function permissions()
    {
        return null;
    }

    public function root(): ?string
    {
        return '/tmp';
    }
}

class BrokenModelWithContent extends ExtendedModelWithContent
{
    public function root(): ?string
    {
        return null;
    }
}

class BlueprintsModelWithContent extends ExtendedModelWithContent
{
    protected $testModel;

    public function __construct(Model $model)
    {
        $this->testModel = $model;
    }

    public function blueprint()
    {
        return new Blueprint([
            'model'  => $this->testModel,
            'name'   => 'model',
            'title'  => 'Model',
            'columns' => [
                [
                    'sections' => [
                        'pages' => [
                            'name' => 'pages',
                            'type' => 'pages',
                            'parent' => 'site',
                            'templates' => [
                                'foo',
                                'bar',
                            ]
                        ],
                        'menu' => [
                            'name' => 'menu',
                            'type' => 'pages',
                            'parent' => 'site',
                            'templates' => [
                                'home',
                                'default',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}

class ModelWithContentTest extends TestCase
{
    public function modelsProvider(): array
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'foo',
                        'files' => [
                            ['filename' => 'a.jpg'],
                            ['filename' => 'b.jpg']
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'c.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com'
                ]
            ]
        ]);

        return [
            [$app->site()],
            [$app->page('foo')],
            [$app->site()->files()->first()],
            [$app->user('test@getkirby.com')]
        ];
    }

    public function testContentLock()
    {
        $model = new ExtendedModelWithContent();
        $this->assertInstanceOf('Kirby\\Cms\\ContentLock', $model->lock());
    }

    public function testContentLockWithNoDirectory()
    {
        $model = new BrokenModelWithContent();
        $this->assertNull($model->lock());
    }

    /**
     * @dataProvider modelsProvider
     * @param \Kirby\Cms\Model $model
     */
    public function testBlueprints($model)
    {
        $model = new BlueprintsModelWithContent($model);
        $this->assertSame([
            [
                'name' => 'foo',
                'title' => 'Foo'
            ],
            [
                'name' => 'bar',
                'title' => 'Bar'
            ],
            [
                'name' => 'home',
                'title' => 'Home'
            ],
            [
                'name' => 'Page',
                'title' => 'Page'
            ]
        ], $model->blueprints());

        $this->assertSame([
            [
                'name' => 'home',
                'title' => 'Home'
            ],
            [
                'name' => 'Page',
                'title' => 'Page'
            ]
        ], $model->blueprints('menu'));
    }
}
