<?php

namespace Kirby\Http\Response;

use PHPUnit\Framework\TestCase;

class DownloadTest extends TestCase
{

    protected $_download = __DIR__ . '/fixtures/download.txt';

    public function testFilename()
    {
        // get the default
        $download = new Download($this->_download);
        $this->assertEquals('download.txt', $download->filename());

        // set custom with constructor
        $download = new Download($this->_download, 'test.txt');
        $this->assertEquals('test.txt', $download->filename());

        // test the filename setter
        $download = new Download($this->_download);
        $this->assertEquals('download.txt', $download->filename());
        $download->filename('test.txt');
        $this->assertEquals('test.txt', $download->filename());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The file could not be found
     */
    public function testMissingFile()
    {
        $download = new Download('does/not/exist.txt');
    }

    public function testModified()
    {
        // default
        $download = new Download($this->_download);
        $this->assertEquals(filemtime($this->_download), $download->modified());

        // custom modified time
        $download = new Download($this->_download);
        $time     = time();

        $this->assertEquals($time, $download->modified($time));
        $this->assertEquals($time, $download->modified());
    }

    public function testSize()
    {
        $download = new Download($this->_download);
        $this->assertEquals(4, $download->size());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        $download = new Download($this->_download);

        ob_start();

        echo $download->send();

        $code = http_response_code();
        $body = ob_get_contents();

        ob_end_clean();

        $this->assertEquals($body, 'test');
        $this->assertEquals($code, 200);
        $this->assertEquals([
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Last-Modified'             => gmdate('D, d M Y H:i:s', filemtime($this->_download)) . ' GMT',
            'Content-Disposition'       => 'attachment; filename="download.txt"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length'            => 4,
            'Connection'                => 'close'
        ], $download->headers());
    }

    public function testToArray()
    {
        $download = new Download($this->_download);
        $expected = [
            'filename' => 'download.txt',
            'type'     => 'application/force-download',
            'charset'  => 'UTF-8',
            'modified' => filemtime($this->_download),
            'code'     => 200,
            'body'     => 'test',
        ];

        $this->assertEquals($expected, $download->toArray());
    }
}
