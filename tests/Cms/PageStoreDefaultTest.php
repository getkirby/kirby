<?php

namespace Kirby\Cms;

class PageStoreDefaultTest extends TestCase
{

    public function testUpdate()
    {

        $page = new Page([
            'slug'    => 'test',
            'content' => [
                'headline' => 'Awesome'
            ]
        ]);

        $store  = new PageStoreDefault($page);
        $result = $store->update($data = ['title' => 'Yay'], $data);

        $this->assertEquals('Yay', $result->title()->value());
        $this->assertEquals('Awesome', $result->headline()->value());
    }

}
