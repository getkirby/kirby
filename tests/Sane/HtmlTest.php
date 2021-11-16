<?php

namespace Kirby\Sane;

/**
 * @covers \Kirby\Sane\Html
 * @todo Add more tests from DOMPurify and the other test classes
 */
class HtmlTest extends TestCase
{
    protected $type = 'html';

    /**
     * @dataProvider allowedProvider
     */
    public function testAllowed(string $file)
    {
        $fixture = $this->fixture($file);

        $this->assertNull(Html::validateFile($fixture));

        $sanitized = Html::sanitize(file_get_contents($fixture));
        $this->assertStringEqualsFile($fixture, $sanitized);
    }

    public function allowedProvider()
    {
        return $this->fixtureList('allowed', 'html');
    }
}
