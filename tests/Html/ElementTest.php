<?php

namespace Kirby\Html;

class InvalidElement {}

class ElementTest extends TestCase
{

    public function testConstructSimple()
    {

        $element = new Element('a');
        $attr    = new Attributes;
        $classes = new ClassList;

        $this->assertEquals('a', $element->tagName());

        // check for an empty attribute list
        $this->assertEquals($attr, $element->attr());

        // check for an empty class list
        $this->assertEquals($classes, $element->classList());

        // check for an empty html body
        $this->assertEquals('', $element->html());

        // check for the result
        $this->assertEquals('<a></a>', $element->toString());

    }

    public function testConstructWithHtml()
    {

        // string
        $element = new Element('a', 'test');
        $this->assertEquals('test', $element->html());

        // array of strings
        $element = new Element('a', ['a', 'b', 'c']);
        $this->assertEquals('abc', $element->html());

        // element
        $element = new Element('a', new Element('span'));
        $this->assertEquals('<span></span>', $element->html());

        // array of elements
        $element = new Element('ul', [new Element('li'), new Element('li')]);
        $this->assertEquals('<li></li><li></li>', $element->html());

    }

    public function testConstructWithAttributes()
    {

        // regular attributes
        $element = new Element('a', ['href' => '#', 'rel' => 'me']);
        $this->assertEquals('#',  $element->attr('href'));
        $this->assertEquals('me', $element->attr('rel'));

        // class
        $element = new Element('a', ['class' => 'link']);
        $this->assertEquals('link',  $element->attr('class'));
        $this->assertTrue($element->hasClass('link'));

    }

    public function testConstructWithHtmlAndAttributes()
    {

        // string + attr
        $element = new Element('a', 'test', ['href' => '#']);
        $this->assertEquals('test', $element->html());
        $this->assertEquals('#',    $element->attr('href'));

        // string + attr + class
        $element = new Element('a', 'test', ['href' => '#', 'class' => 'link']);
        $this->assertEquals('test',  $element->html());
        $this->assertEquals('#',     $element->attr('href'));
        $this->assertEquals('link',  $element->attr('class'));
        $this->assertTrue($element->hasClass('link'));

        // array + attr
        $element = new Element('a', ['a', 'b', 'c'], ['href' => '#']);
        $this->assertEquals('abc', $element->html());
        $this->assertEquals('#',   $element->attr('href'));

        // Element + attr
        $element = new Element('a', new Element('span'), ['href' => '#']);
        $this->assertEquals('<span></span>', $element->html());
        $this->assertEquals('#', $element->attr('href'));

        // array of Elements + attr
        $element = new Element('a', [new Element('span'), new Element('span')], ['href' => '#']);
        $this->assertEquals('<span></span><span></span>', $element->html());
        $this->assertEquals('#', $element->attr('href'));

    }

    public function testTagName()
    {

        // lowercase
        $element = new Element('a');
        $this->assertEquals('a', $element->tagName());

        // uppercase
        $element = new Element('A');
        $this->assertEquals('a', $element->tagName());

    }

    public function testHtml()
    {

        // string
        $element = new Element('a');
        $result  = $element->html('test');

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('test', $element->html());

        // array of strings
        $element = new Element('a');
        $result  = $element->html(['a', 'b', 'c']);

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('abc', $element->html());

        // element
        $element = new Element('a');
        $result  = $element->html(new Element('span'));

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('<span></span>', $element->html());

        // array of elements
        $element = new Element('ul');
        $result  = $element->html([new Element('li'), new Element('li')]);

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('<li></li><li></li>', $element->html());

    }

    public function testVoidList()
    {

        $expected = [
          'area',
          'base',
          'br',
          'col',
          'command',
          'embed',
          'hr',
          'img',
          'input',
          'keygen',
          'link',
          'meta',
          'param',
          'source',
          'track',
          'wbr',
        ];

        $this->assertEquals($expected, Element::$void);

    }

    public function testText()
    {

        $element = new Element('a');
        $result  = $element->text('öäü');

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('&ouml;&auml;&uuml;', $element->html());

    }

    public function testIsVoid()
    {

        // void
        $voidElement = new Element('img');
        $this->assertTrue($voidElement->isVoid());

        // non-void
        $voidElement = new Element('div');
        $this->assertFalse($voidElement->isVoid());

    }

    public function testClassList()
    {

        $element = new Element('a');

        $this->assertInstanceOf('Kirby\\Html\\ClassList', $element->classList());
        $this->assertEquals('', $element->classList()->toString());

    }

    public function testAttrSetters()
    {

        // single attribute
        $element = new Element('a');
        $result  = $element->attr('rel', 'me');

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('me', $element->attr('rel'));

        // multiple attributes
        $element = new Element('a');
        $result  = $element->attr([
            'rel'  => 'me',
            'href' => '#'
        ]);

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('me', $element->attr('rel'));
        $this->assertEquals('#', $element->attr('href'));

        // class setter
        $element = new Element('a');
        $result  = $element->attr('class', 'link');

        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertEquals('link', $element->attr('class'));

        // class setter overwrites the existing class list
        $element = new Element('a');
        $element->attr('class', 'link');
        $this->assertEquals('link', $element->attr('class'));
        $element->attr('class', 'button');
        $this->assertEquals('button', $element->attr('class'));

    }

    public function testAttrGetters()
    {

        // single attribute getter
        $element = new Element('a');
        $element->attr('rel', 'me');

        $this->assertEquals('me', $element->attr('rel'));

        // getter for all attributes
        $element = new Element('a');
        $element->attr('rel', 'me');
        $element->attr('href', '#');

        $this->assertInstanceOf('Kirby\\Html\\Attributes', $element->attr());
        $this->assertEquals('me', $element->attr()->get('rel'));
        $this->assertEquals('#', $element->attr()->get('href'));

        // getter for the class list
        $element = new Element('a');
        $element->attr('class', 'link');

        $this->assertInstanceOf('Kirby\\Html\\ClassList', $element->attr('class'));
        $this->assertEquals('link', $element->attr('class'));

    }

    public function testHasClass()
    {

        // constructor
        $element = new Element('a', ['class' => 'link']);
        $this->assertTrue($element->hasClass('link'));
        $this->assertFalse($element->hasClass('button'));

        // attr setter
        $element = new Element('a');
        $element->attr('class', 'link');
        $this->assertTrue($element->hasClass('link'));
        $this->assertFalse($element->hasClass('button'));

        // addClass
        $element = new Element('a');
        $element->addClass('link');
        $this->assertTrue($element->hasClass('link'));
        $this->assertFalse($element->hasClass('button'));

        // multiple classes
        $element = new Element('a');
        $element->addClass('link');
        $element->addClass('link-primary');
        $this->assertTrue($element->hasClass('link'));
        $this->assertTrue($element->hasClass('link-primary'));
        $this->assertFalse($element->hasClass('button'));

    }

    public function testAddClass()
    {

        // single class
        $element = new Element('a');
        $this->assertFalse($element->hasClass('link'));
        $this->assertFalse($element->hasClass('button'));

        $result = $element->addClass('link');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertTrue($element->hasClass('link'));
        $this->assertFalse($element->hasClass('button'));

        // multiple classes
        $element = new Element('a');
        $this->assertFalse($element->hasClass('link'));
        $this->assertFalse($element->hasClass('link-primary'));
        $this->assertFalse($element->hasClass('button'));

        $result = $element->addClass('link', 'link-primary');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertTrue($element->hasClass('link'));
        $this->assertTrue($element->hasClass('link-primary'));
        $this->assertFalse($element->hasClass('button'));

    }

    public function testToggleClass()
    {

        $element = new Element('a');
        $this->assertFalse($element->hasClass('link'));

        $result = $element->toggleClass('link');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertTrue($element->hasClass('link'));

        $result = $element->toggleClass('link');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertFalse($element->hasClass('link'));
    }

    public function testRemoveClass()
    {

        // single class
        $element = new Element('a');
        $element->addClass('link');
        $this->assertTrue($element->hasClass('link'));

        $result = $element->removeClass('link');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertFalse($element->hasClass('link'));

        // multiple classes
        $element = new Element('a');
        $element->addClass('link', 'link-primary');
        $this->assertTrue($element->hasClass('link'));
        $this->assertTrue($element->hasClass('link-primary'));

        $result = $element->removeClass('link', 'link-primary');
        $this->assertInstanceOf('Kirby\\Html\\Element', $result);
        $this->assertFalse($element->hasClass('link'));
        $this->assertFalse($element->hasClass('link-primary'));

    }

    public function testBegin()
    {

        // simple
        $element = new Element('a');
        $this->assertEquals('<a>', $element->begin());

        // with single attribute
        $element = new Element('a');
        $element->attr('href', '#');
        $this->assertEquals('<a href="#">', $element->begin());

        // with multiple attribute
        $element = new Element('a');
        $element->attr('rel', 'me');
        $element->attr('href', '#');
        $this->assertEquals('<a href="#" rel="me">', $element->begin());

        // with multiple attributes + class
        $element = new Element('a');
        $element->attr('rel', 'me');
        $element->attr('href', '#');
        $element->addClass('link');
        $this->assertEquals('<a class="link" href="#" rel="me">', $element->begin());

        // with multiple attributes + multiple classes
        $element = new Element('a');
        $element->attr('rel', 'me');
        $element->attr('href', '#');
        $element->addClass('link', 'link-primary');
        $this->assertEquals('<a class="link link-primary" href="#" rel="me">', $element->begin());

    }

    public function testEnd()
    {

        // regular
        $element = new Element('a');
        $this->assertEquals('</a>', $element->end());

        // void
        foreach(Element::$void as $tagName) {
            $element = new Element($tagName);
            $this->assertEquals('', $element->end());
        }

    }

    public function testWrap()
    {

        // with string
        $element = new Element('a');
        $wrapper = $element->wrap('div');

        $this->assertInstanceOf('Kirby\\Html\\Element', $wrapper);
        $this->assertEquals('<div><a></a></div>', $wrapper->toString());

        // with element
        $elementA = new Element('a');
        $elementB = new Element('div');
        $wrapper  = $elementA->wrap($elementB);

        $this->assertInstanceOf('Kirby\\Html\\Element', $wrapper);
        $this->assertEquals($elementB, $wrapper);
        $this->assertEquals('<div><a></a></div>', $wrapper->toString());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid wrapper element. Must be an extension of the Kirby\Html\Element class
     */
    public function testWrapWithInvalidElement()
    {
        $elementA = new Element('a');
        $elementB = new InvalidElement();
        $wrapper  = $elementA->wrap($elementB);
    }

    public function testToString()
    {
        $tests = [
            // simple
            [
                'tag'      => 'a',
                'html'     => '',
                'attr'     => [],
                'expected' => '<a></a>',
            ],
            // with attribute
            [
                'tag'      => 'a',
                'html'     => '',
                'attr'     => ['href' => '#'],
                'expected' => '<a href="#"></a>',
            ],
            // with multiple attributes
            [
                'tag'      => 'a',
                'html'     => '',
                'attr'     => ['rel' => 'me', 'href' => '#'],
                'expected' => '<a href="#" rel="me"></a>',
            ],
            // with multiple attributes + class
            [
                'tag'      => 'a',
                'html'     => '',
                'attr'     => ['rel' => 'me', 'href' => '#', 'class' => 'link'],
                'expected' => '<a class="link" href="#" rel="me"></a>',
            ],
            // with multiple attributes + class + html string
            [
                'tag'      => 'a',
                'html'     => 'test',
                'attr'     => ['rel' => 'me', 'href' => '#', 'class' => 'link'],
                'expected' => '<a class="link" href="#" rel="me">test</a>',
            ],
            // with multiple attributes + class + html array
            [
                'tag'      => 'a',
                'html'     => ['a', 'b', 'c'],
                'attr'     => ['rel' => 'me', 'href' => '#', 'class' => 'link'],
                'expected' => '<a class="link" href="#" rel="me">abc</a>',
            ],
            // with multiple attributes + class + html Element
            [
                'tag'      => 'a',
                'html'     => new Element('span'),
                'attr'     => ['rel' => 'me', 'href' => '#', 'class' => 'link'],
                'expected' => '<a class="link" href="#" rel="me"><span></span></a>',
            ],
            // with multiple attributes + class + html array of Elements
            [
                'tag'      => 'a',
                'html'     => [new Element('span'), new Element('span')],
                'attr'     => ['rel' => 'me', 'href' => '#', 'class' => 'link'],
                'expected' => '<a class="link" href="#" rel="me"><span></span><span></span></a>',
            ],
            // simple void attribute
            [
                'tag'      => 'img',
                'html'     => '',
                'attr'     => [],
                'expected' => '<img>',
            ],
            // void attribute with all bells and whistles
            [
                'tag'      => 'img',
                'html'     => '',
                'attr'     => ['src' => 'image.jpg', 'alt' => 'test', 'class' => 'image'],
                'expected' => '<img alt="test" class="image" src="image.jpg">',
            ],
        ];

        foreach($tests as $test) {
            $element = new Element($test['tag'], $test['html'], $test['attr']);
            $this->assertEquals($test['expected'], $element->toString());
            $this->assertEquals($test['expected'], $element->__toString());
            $this->assertEquals($test['expected'], $element);
        }

    }

}
