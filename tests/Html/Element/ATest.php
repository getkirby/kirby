<?php

namespace Kirby\Html\Element;

use Kirby\Html\TestCase;

class ATest extends TestCase
{

    public function testConstruct()
    {

        // default
        $a = new A;

        $this->assertEquals('/', $a->attr('href'));
        $this->assertEquals('', $a->html());
        $this->assertEquals('<a href="/"></a>', $a);

        // custom url
        $a = new A('https://getkirby.com');

        $this->assertEquals('https://getkirby.com', $a->attr('href'));
        $this->assertEquals('<a href="https://getkirby.com"></a>', $a);

        // custom url + custom text
        $a = new A('https://getkirby.com', 'Kirby');

        $this->assertEquals('Kirby', $a->html());
        $this->assertEquals('https://getkirby.com', $a->attr('href'));
        $this->assertEquals('<a href="https://getkirby.com">Kirby</a>', $a);

        // custom url + custom text + attributes
        $a = new A('https://getkirby.com', 'Kirby', [
            'class' => 'link'
        ]);

        $this->assertEquals('Kirby', $a->html());
        $this->assertEquals('https://getkirby.com', $a->attr('href'));
        $this->assertEquals('<a class="link" href="https://getkirby.com">Kirby</a>', $a);

    }

}
