<?php

namespace Kirby\Cms;

class SiteActionsTest extends TestCase
{

    public function setUp()
    {
        App::removePlugins();
    }

    public function testUpdate()
    {
        $site = new Site();
        $site = $site->clone([
            'blueprint' => new SiteBlueprint([
                'name'   => 'test',
                'title'  => 'test',
                'model'  => $site,
                'fields' => [
                    'headline' => [
                        'type' => 'text'
                    ],
                    'text' => [
                        'type' => 'text'
                    ]
                ]
            ])
        ]);

        $this->assertHooks([
            'site.update:before' => function (Site $site, array $values, array $strings) {
                $this->assertEquals(null, $site->headline()->value());
                $this->assertEquals(null, $site->text()->value());

                $this->assertEquals('Test', $strings['headline']);
                $this->assertEquals('Test', $strings['text']);
            },
            'site.update:after' => function (Site $newSite, Site $oldSite) {
                $this->assertEquals('Test', $newSite->headline()->value());
                $this->assertEquals(null, $oldSite->headline()->value());
            }
        ], function () use ($site) {
            $site->update([
                'headline' => 'Test',
                'text'     => 'Test'
            ]);
        });
    }

}
