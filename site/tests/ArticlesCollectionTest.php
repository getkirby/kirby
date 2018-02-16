<?php

namespace Kirby\Cms;

class ArticlesCollectionTest extends CollectionTestCase
{

    public $collection     = 'articles';
    public $collectionType = Pages::class;

    public function testItems()
    {
        $this->assertCollectionCount(3);
        $this->assertCollectionHasNoPagination();
    }

}
