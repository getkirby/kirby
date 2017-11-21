<?php

namespace Kirby\Toolkit\Url;

use Kirby\Toolkit\Url;

class TestCase extends \PHPUnit\Framework\TestCase
{

    protected $_yt   = 'http://www.youtube.com/watch?v=9q_aXttJduk';
    protected $_yts  = 'https://www.youtube.com/watch?v=9q_aXttJduk';
    protected $_docs = 'http://getkirby.com/docs/';
    public static function setUpBeforeClass()
    {
        Url::$current = 'https://www.youtube.com/watch?v=9q_aXttJduk';
    }

    public static function tearDownAfterClass()
    {
        Url::$current = null;
    }
}
