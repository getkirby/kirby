<?php

namespace Kirby\Toolkit;

use SimpleXMLElement;

/**
 * Mime type detection/guessing
 */
class Mime
{
    public static $types = [
        'ai'    => 'application/postscript',
        'aif'   => 'audio/x-aiff',
        'aifc'  => 'audio/x-aiff',
        'aiff'  => 'audio/x-aiff',
        'avi'   => 'video/x-msvideo',
        'bmp'   => 'image/bmp',
        'css'   => 'text/css',
        'csv'   => ['text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'],
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dvi'   => 'application/x-dvi',
        'eml'   => 'message/rfc822',
        'eps'   => 'application/postscript',
        'exe'   => ['application/octet-stream', 'application/x-msdownload'],
        'gif'   => 'image/gif',
        'gtar'  => 'application/x-gtar',
        'gz'    => 'application/x-gzip',
        'htm'   => 'text/html',
        'html'  => 'text/html',
        'ico'   => 'image/x-icon',
        'ics'   => 'text/calendar',
        'js'    => 'application/x-javascript',
        'json'  => ['application/json', 'text/json'],
        'jpg'   => ['image/jpeg', 'image/pjpeg'],
        'jpeg'  => ['image/jpeg', 'image/pjpeg'],
        'jpe'   => ['image/jpeg', 'image/pjpeg'],
        'log'   => ['text/plain', 'text/x-log'],
        'm4a'   => 'audio/mp4',
        'm4v'   => 'video/mp4',
        'mid'   => 'audio/midi',
        'midi'  => 'audio/midi',
        'mif'   => 'application/vnd.mif',
        'mov'   => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mp2'   => 'audio/mpeg',
        'mp3'   => ['audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'],
        'mp4'   => 'video/mp4',
        'mpe'   => 'video/mpeg',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpga'  => 'audio/mpeg',
        'odc'   => 'application/vnd.oasis.opendocument.chart',
        'odp'   => 'application/vnd.oasis.opendocument.presentation',
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'pdf'   => ['application/pdf', 'application/x-download'],
        'php'   => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'php3'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'phps'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'phtml' => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'png'   => 'image/png',
        'ppt'   => ['application/powerpoint', 'application/vnd.ms-powerpoint'],
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ps'    => 'application/postscript',
        'psd'   => 'application/x-photoshop',
        'qt'    => 'video/quicktime',
        'rtf'   => 'text/rtf',
        'rtx'   => 'text/richtext',
        'shtml' => 'text/html',
        'svg'   => 'image/svg+xml',
        'swf'   => 'application/x-shockwave-flash',
        'tar'   => 'application/x-tar',
        'text'  => 'text/plain',
        'txt'   => 'text/plain',
        'tgz'   => ['application/x-tar', 'application/x-gzip-compressed'],
        'tif'   => 'image/tiff',
        'tiff'  => 'image/tiff',
        'wav'   => 'audio/x-wav',
        'wbxml' => 'application/wbxml',
        'webm'  => 'video/webm',
        'webp'  => 'image/webp',
        'word'  => ['application/msword', 'application/octet-stream'],
        'xhtml' => 'application/xhtml+xml',
        'xht'   => 'application/xhtml+xml',
        'xml'   => 'text/xml',
        'xl'    => 'application/excel',
        'xls'   => ['application/excel', 'application/vnd.ms-excel', 'application/msexcel'],
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xsl'   => 'text/xml',
        'zip'   => ['application/x-zip', 'application/zip', 'application/x-zip-compressed'],
    ];

    public static function fromExtension(string $extension)
    {
        $mime = static::$types[$extension] ?? null;
        return is_array($mime) === true ? array_shift($mime) : $mime;
    }

    /**
     * Returns the mime type of a file
     *
     * @param string $file
     * @return string|false
     */
    public static function fromFileInfo(string $file)
    {
        if (function_exists('finfo_file') === true && file_exists($file) === true) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file);
            finfo_close($finfo);
            return $mime;
        }

        return false;
    }

    /**
     * Returns the mime type of a file
     *
     * @param string $file
     * @return string|false
     */
    public static function fromMimeContentType(string $file)
    {
        if (function_exists('mime_content_type') === true && file_exists($file) === true) {
            return mime_content_type($file);
        }

        return false;
    }

    public static function fromSvg(string $file)
    {
        if (file_exists($file) === true) {
            libxml_use_internal_errors(true);

            $svg = new SimpleXMLElement(file_get_contents($file));

            if ($svg !== false && $svg->getName() === 'svg') {
                return 'image/svg+xml';
            }
        }

        return false;
    }

    public static function toExtension(string $mime = null)
    {
        foreach (static::$types as $key => $value) {
            if (is_array($value) === true && in_array($mime, $value) === true) {
                return $key;
            }

            if ($value === $mime) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Returns the mime type of a file
     *
     * @param string $file
     * @return string|false
     */
    public static function type(string $file, string $extension = null)
    {
        // use the standard finfo extension
        $mime = static::fromFileInfo($file);

        // use the mime_content_type function
        if ($mime === false) {
            $mime = static::fromMimeContentType($file);
        }

        // get the extension or extract it from the filename
        $extension = $extension ?? F::extension($file);

        // try to guess the mime type at least
        if ($mime === false) {
            $mime = static::fromExtension($extension);
        }

        // fix broken mime detection for svg files with style attribute
        if (in_array($mime, ['text/html', 'text/plain']) === true && $extension === 'svg') {
            $mime = static::fromSvg($file);
        }

        // normalize image/svg file type
        if ($mime === 'image/svg') {
            $mime = 'image/svg+xml';
        }

        return $mime;
    }

    /**
     * Returns all detectable mime types
     *
     * @return array
     */
    public static function types(): array
    {
        return static::$types;
    }
}
