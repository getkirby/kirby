<?php

namespace Kirby\Cms;

use Kirby\Cms\App;
use Kirby\Data\Yaml;

class FieldMethodsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function field($value = '')
    {
        return new Field(null, 'test', $value);
    }

    public function testFieldMethodCombination()
    {
        $field = $this->field('test')->upper()->short(3);
        $this->assertEquals('TES…', $field->value());
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->field()->isEmpty());
    }

    public function testIsFalse()
    {
        $this->assertTrue($this->field('false')->isFalse());
        $this->assertTrue($this->field(false)->isFalse());
    }

    public function testIsNotEmpty()
    {
        $this->assertTrue($this->field('test')->isNotEmpty());
    }

    public function testIsTrue()
    {
        $this->assertTrue($this->field('true')->isTrue());
        $this->assertTrue($this->field(true)->isTrue());
    }

    public function testIsValid()
    {
        $this->assertTrue($this->field('mail@example.com')->isValid('email'));
        $this->assertTrue($this->field('https://example.com')->isValid('url'));
    }

    public function testToDataSplit()
    {
        $this->assertEquals(['a', 'b'], $this->field('a, b')->toData());
    }

    public function testToDataSplitWithDifferentSeparator()
    {
        $this->assertEquals(['a', 'b'], $this->field('a; b')->toData(';'));
    }

    public function testToDataYaml()
    {
        $data = ['a', 'b'];

        $this->assertEquals(['a', 'b'], $this->field(Yaml::encode($data))->toData('yaml'));
    }

    public function testToDataJson()
    {
        $data = ['a', 'b'];

        $this->assertEquals(['a', 'b'], $this->field(json_encode($data))->toData('json'));
    }

    public function testToBool()
    {
        $this->assertTrue($this->field('1')->toBool());
        $this->assertTrue($this->field('true')->toBool());
        $this->assertFalse($this->field('0')->toBool());
        $this->assertFalse($this->field('false')->toBool());
    }

    public function testToDate()
    {
        $field = $this->field('2012-12-12');
        $ts    = strtotime('2012-12-12');
        $date  = '12.12.2012';

        $this->assertEquals($ts, $field->toDate());
        $this->assertEquals($date, $field->toDate('d.m.Y'));
    }

    public function testToDateWithDateHandler()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'date.handler' => 'strftime'
            ]
        ]);

        $field = $this->field('2012-12-12');
        $ts    = strtotime('2012-12-12');
        $date  = '12.12.2012';

        $this->assertEquals($ts, $field->toDate());
        $this->assertEquals($date, $field->toDate('%d.%m.%Y'));
    }

    public function testToFile()
    {
        $page = new Page([
            'content' => [
                'cover' => 'cover.jpg'
            ],
            'files' => [
                ['filename' => 'cover.jpg']
            ],
            'slug' => 'test'
        ]);

        $this->assertEquals('cover.jpg', $page->cover()->toFile()->filename());
    }

    public function testToFiles()
    {
        $page = new Page([
            'content' => [
                'gallery' => Yaml::encode(['a.jpg', 'b.jpg'])
            ],
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg']
            ],
            'slug' => 'test'
        ]);

        $this->assertEquals($page->files()->pluck('filename'), $page->gallery()->toFiles()->pluck('filename'));
    }

    public function testToFilesFromDifferentPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'gallery' => Yaml::encode(['b/b.jpg', 'a/a.jpg'])
                        ],
                        'files' => [
                            ['filename' => 'a.jpg']
                        ]
                    ],
                    [
                        'slug' => 'b',
                        'files' => [
                            ['filename' => 'b.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('a');

        $this->assertEquals(['b.jpg', 'a.jpg'], $page->gallery()->toFiles()->pluck('filename'));
    }

    public function testToFilesWithoutResults()
    {
        $page = new Page([
            'content' => [
                'gallery' => Yaml::encode(['a.jpg', 'b.jpg'])
            ],
            'files' => [
            ],
            'slug' => 'test'
        ]);

        $this->assertInstanceOf(Files::class, $page->gallery()->toFiles());
    }

    public function testToFloat()
    {
        $field    = $this->field('1.2');
        $expected = 1.2;

        $this->assertEquals($expected, $field->toFloat());
    }

    public function testToInt()
    {
        $this->assertEquals(1, $this->field('1')->toInt());
        $this->assertTrue(is_int($this->field('1')->toInt()));
    }

    public function testToLink()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'title' => 'Test'
            ]
        ]);

        $expected = '<a href="/test">Test</a>';

        $this->assertEquals($expected, $page->title()->toLink());
    }

    public function testToPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b']
                ]
            ]
        ]);

        $a = $app->page('a');
        $b = $app->page('b');

        $this->assertEquals($a, $this->field('a')->toPage());
        $this->assertEquals($b, $this->field('b')->toPage());

        $this->assertEquals($a, $this->field(Yaml::encode(['a']))->toPage());
        $this->assertEquals($b, $this->field(Yaml::encode(['b', 'a']))->toPage());
    }

    public function testToPages()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                ]
            ]
        ]);

        $a = $app->page('a');
        $b = $app->page('b');

        // single page
        $pages = new Pages([$a], $app->site());

        $content = Yaml::encode([
            'a',
        ]);

        $this->assertEquals($pages, $this->field($content)->toPages());

        // multiple pages
        $pages = new Pages([$a, $b], $app->site());

        $content = Yaml::encode([
            'a',
            'b'
        ]);

        $this->assertEquals($pages, $this->field($content)->toPages());

        // no results
        $content = Yaml::encode([
            'c',
            'd'
        ]);

        $this->assertInstanceOf(Pages::class, $this->field($content)->toPages());
    }

    public function testToStructure()
    {
        $data = [
            ['title' => 'a'],
            ['title' => 'b']
        ];

        $yaml = Yaml::encode($data);

        $field     = $this->field($yaml);
        $structure = $field->toStructure();

        $this->assertCount(2, $structure);
        $this->assertEquals('a', $structure->first()->title()->value());
        $this->assertEquals('b', $structure->last()->title()->value());
    }

    public function testToDefaultUrl()
    {
        $field    = $this->field('super/cool');
        $expected = '/super/cool';

        $this->assertEquals($expected, $field->toUrl());
    }

    public function testToCustomUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);

        $field    = $this->field('super/cool');
        $expected = 'https://getkirby.com/super/cool';

        $this->assertEquals($expected, $field->toUrl());
    }

    public function testToUser()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@company.com'],
                ['email' => 'b@company.com']
            ]
        ]);

        $a = $app->user('a@company.com');
        $b = $app->user('b@company.com');

        $this->assertEquals($a, $this->field('a@company.com')->toUser());
        $this->assertEquals($b, $this->field('b@company.com')->toUser());

        $this->assertEquals($a, $this->field(Yaml::encode(['a@company.com']))->toUser());
        $this->assertEquals($b, $this->field(Yaml::encode(['b@company.com', 'a@company.com']))->toUser());
    }

    public function testToUsers()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                ['email' => 'a@company.com'],
                ['email' => 'b@company.com']
            ]
        ]);

        // two results
        $content = Yaml::encode([
            'a@company.com',
            'b@company.com'
        ]);

        $this->assertEquals(['a@company.com', 'b@company.com'], $this->field($content)->toUsers()->pluck('email'));

        // no results
        $content = Yaml::encode([
            'c@company.com',
            'd@company.com'
        ]);

        $this->assertInstanceOf(Users::class, $this->field($content)->toUsers());
    }

    public function testLength()
    {
        $this->assertEquals(3, $this->field('abc')->length());
    }

    public function testEscape()
    {
        $this->assertEquals('&lt;script&gt;alert(&quot;hello&quot;)&lt;/script&gt;', $this->field('<script>alert("hello")</script>')->escape());
    }

    public function testExcerpt()
    {
        // html
        $string   = 'This is a long text<br>with some html';
        $expected = 'This is a long text with …';

        $this->assertEquals($expected, $this->field($string)->excerpt(27)->value());

        // markdown
        $string   = 'This is a long text **with some** html';
        $expected = 'This is a long text with …';

        $this->assertEquals($expected, $this->field($string)->excerpt(27)->value());
    }

    public function testHtml()
    {
        $this->assertEquals('&ouml;', $this->field('ö')->html());
    }

    public function testKirbytext()
    {
        $kirbytext = '(link: # text: Test)';
        $expected  = '<p><a href="#">Test</a></p>';

        $this->assertEquals($expected, $this->field($kirbytext)->kirbytext());
        $this->assertEquals($expected, $this->field($kirbytext)->kt());
    }

    public function testKirbytags()
    {
        $kirbytext = '(link: # text: Test)';
        $expected  = '<a href="#">Test</a>';

        $this->assertEquals($expected, $this->field($kirbytext)->kirbytags());
    }

    public function testLower()
    {
        $this->assertEquals('abc', $this->field('ABC')->lower());
    }

    public function testMarkdown()
    {
        $markdown = '**Test**';
        $expected = '<p><strong>Test</strong></p>';

        $this->assertEquals($expected, $this->field($markdown)->markdown());
    }

    public function testOr()
    {
        $this->assertEquals('field value', $this->field('field value')->or('fallback')->value());
        $this->assertEquals('fallback', $this->field()->or('fallback')->value());
    }

    public function testShort()
    {
        $this->assertEquals('abc…', $this->field('abcd')->short(3));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->field($text)->smartypants());
    }

    public function testSlug()
    {
        $text     = 'Ä--Ö--Ü';
        $expected = 'a-o-u';

        $this->assertEquals($expected, $this->field($text)->slug()->value());
    }

    public function testSplit()
    {
        $text = 'a, b, c';
        $expected = ['a', 'b', 'c'];

        $this->assertEquals($expected, $this->field($text)->split());
    }

    public function testUpper()
    {
        $this->assertEquals('ABC', $this->field('abc')->upper());
    }

    public function testWidont()
    {
        $this->assertEquals('Test&nbsp;Headline', $this->field('Test Headline')->widont());
    }

    public function testWords()
    {
        $text = 'this is an example text';
        $this->assertEquals(5, $this->field($text)->words());
    }

    public function testXml()
    {
        $this->assertEquals('&#246;&#228;&#252;', $this->field('öäü')->xml()->value());
    }

    public function testYaml()
    {
        $data = [
            'a',
            'b',
            'c'
        ];

        $yaml = Yaml::encode($data);
        $this->assertEquals($data, $this->field($yaml)->yaml());
    }
}
