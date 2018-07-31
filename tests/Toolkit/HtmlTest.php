<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{

    public function testA()
    {
        $html = Html::a('https://getkirby.com');
        $expected = '<a href="https://getkirby.com">https://getkirby.com</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithText()
    {
        $html = Html::a('https://getkirby.com', 'Kirby');
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithAttributes()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['class' => 'test']);
        $expected = '<a class="test" href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithTarget()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank']);
        $expected = '<a href="https://getkirby.com" rel="noopener noreferrer" target="_blank">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAttr()
    {

        $tests = [
            [
                'input'    => [],
                'expected' => ''
            ],
            [
                'input'    => ['a' => 'a', 'b' => 'b'],
                'expected' => 'a="a" b="b"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => true],
                'expected' => 'a="a" b'
            ],
            [
                'input'    => ['a' => 'a', 'b' => ''],
                'expected' => 'a="a"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => false],
                'expected' => 'a="a"'
            ],
        ];

        foreach($tests as $test) {
            $result = Html::attr($test['input']);
            $this->assertEquals($test['expected'], $result);
        }

    }

    public function testBreaks()
    {
        $html = Html::breaks("line 1\nline 2");
        $expected = "line 1<br />\nline 2";

        $this->assertEquals($expected, $html);
    }

    public function testDecode()
    {
        $html = Html::decode('some uber <em>crazy</em> stuff');
        $expected = 'some uber crazy stuff';

        $this->assertEquals($expected, $html);
    }

    public function testEmail()
    {
        $html = Html::email('mail@company.com');
        $expected = '!\<a href=".*?">.*?\</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithText()
    {
        $html = Html::email('mail@company.com', 'Email');
        $expected = '!\<a href=".*?">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithAttributes()
    {
        $html = Html::email('mail@company.com', 'Email', ['class' => 'email']);
        $expected = '!\<a class="email" href=".*?">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithTarget()
    {
        $html = Html::email('mail@company.com', 'Email', ['target' => '_blank']);
        $expected = '!\<a href=".*?" rel="noopener noreferrer" target="_blank">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEncode()
    {
        $html = Html::encode('äöü');
        $expected = '&auml;&ouml;&uuml;';

        $this->assertEquals($expected, $html);
    }

    public function testEncodeWithHtml()
    {
        $html = Html::encode('ä<p>ö</p>', true);
        $expected = '&auml;<p>&ouml;</p>';

        $this->assertEquals($expected, $html);
    }

    public function testFigure()
    {
        $html = Html::figure('test');
        $expected = '<figure>test</figure>';

        $this->assertEquals($expected, $html);
    }

    public function testFigureWithAttributes()
    {
        $html = Html::figure('test', null, ['class' => 'figure']);
        $expected = '<figure class="figure">test</figure>';

        $this->assertEquals($expected, $html);
    }

    public function testFigureWithCaption()
    {
        $html = Html::figure('test', 'yay');
        $expected = '<figure>test<figcaption>yay</figcaption></figure>';

        $this->assertEquals($expected, $html);
    }

    public function testGist()
    {
        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff');
        $expected = '<script src="' . $url . '.js"></script>';

        $this->assertEquals($expected, $html);
    }

    public function testGistWithFile()
    {
        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff', 'kirbycontent.txt');
        $expected = '<script src="' . $url . '.js?file=kirbycontent.txt"></script>';

        $this->assertEquals($expected, $html);
    }

    public function testIframe()
    {
        $html = Html::iframe($url = 'https://getkirby.com');
        $expected = '<iframe src="' . $url . '"></iframe>';

        $this->assertEquals($expected, $html);
    }

    public function testImg()
    {
        $html = Html::img($src = 'https://getkirby.com/image.jpg');
        $expected = '<img alt="" src="' . $src . '">';

        $this->assertEquals($expected, $html);
    }

    public function testRel()
    {
        $html = Html::rel('me');
        $expected = 'me';

        $this->assertEquals($expected, $html);
    }

    public function testRelWithTarget()
    {
        $html = Html::rel(null, '_blank');
        $expected = 'noopener noreferrer';

        $this->assertEquals($expected, $html);

        $html = Html::rel('me', '_blank');
        $expected = 'me noopener noreferrer';

        $this->assertEquals($expected, $html);
    }

}
