<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element\A;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    public function _link()
    {
        $tag  = new Link();
        $tag->parse('http://google.com', [
            'text'   => 'Google',
            'class'  => 'testclass',
            'role'   => 'testrole',
            'title'  => 'Google',
            'rel'    => 'nofollow',
            'target' => '_blank',
            'popup'  => true
        ]);
        return $tag;
    }

    public function testAttributes()
    {
        $tag = $this->_link();
        $this->assertEquals([
            'text',
            'class',
            'role',
            'title',
            'rel',
            'target',
            'popup'
        ], $tag->attributes());
    }


    public function testParse()
    {
        $tag = $this->_link();
        $this->assertEquals('Google', $tag->attr('text'));
        $this->assertEquals('testclass', $tag->attr('class'));
        $this->assertEquals('testrole', $tag->attr('role'));
        $this->assertEquals('Google', $tag->attr('title'));
        $this->assertEquals('nofollow', $tag->attr('rel'));
        $this->assertEquals('_blank', $tag->attr('target'));
        $this->assertEquals(true, $tag->attr('popup'));
    }

    public function testHtml()
    {
        $tag = $this->_link();
        $this->assertEquals('<a class="testclass" href="http://google.com" rel="noopener nofollow" role="testrole" target="_blank" title="Google">Google</a>', (string)$tag);
    }

    public function testTarget()
    {
        $withBlank    = '<a href="http://google.com" rel="noopener nofollow" target="_blank">http://google.com</a>';
        $withoutBlank = '<a href="http://google.com">http://google.com</a>';

        $tag = new Link();
        $tag->parse('http://google.com', ['popup'  => true]);
        $this->assertEquals($withBlank, (string)$tag);

        $tag->parse('http://google.com', ['popup'  => false]);
        $this->assertEquals($withoutBlank, (string)$tag);

        $tag->parse('http://google.com', ['popup'  => false, 'target' => '_blank']);
        $this->assertEquals($withBlank, (string)$tag);

        $tag->parse('http://google.com', ['popup'  => true, 'target' => '_blank']);
        $this->assertEquals($withBlank, (string)$tag);
    }
}
