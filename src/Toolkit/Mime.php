<?php

namespace Kirby\Toolkit;

use SimpleXMLElement;

/**
 * Mime type detection/guessing
 */
class Mime
{
    public static $types = [
        'hqx'   => 'application/mac-binhex40',
        'cpt'   => 'application/mac-compactpro',
        'csv'   => ['text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'],
        'bin'   => 'application/macbinary',
        'dms'   => 'application/octet-stream',
        'lha'   => 'application/octet-stream',
        'lzh'   => 'application/octet-stream',
        'exe'   => ['application/octet-stream', 'application/x-msdownload'],
        'class' => 'application/octet-stream',
        'psd'   => 'application/x-photoshop',
        'so'    => 'application/octet-stream',
        'sea'   => 'application/octet-stream',
        'dll'   => 'application/octet-stream',
        'oda'   => 'application/oda',
        'pdf'   => ['application/pdf', 'application/x-download'],
        'ai'    => 'application/postscript',
        'eps'   => 'application/postscript',
        'ps'    => 'application/postscript',
        'smi'   => 'application/smil',
        'smil'  => 'application/smil',
        'mif'   => 'application/vnd.mif',
        'wbxml' => 'application/wbxml',
        'wmlc'  => 'application/wmlc',
        'dcr'   => 'application/x-director',
        'dir'   => 'application/x-director',
        'dxr'   => 'application/x-director',
        'dvi'   => 'application/x-dvi',
        'gtar'  => 'application/x-gtar',
        'gz'    => 'application/x-gzip',
        'php'   => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'php3'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'phtml' => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'phps'  => ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'],
        'js'    => 'application/x-javascript',
        'swf'   => 'application/x-shockwave-flash',
        'sit'   => 'application/x-stuffit',
        'tar'   => 'application/x-tar',
        'tgz'   => ['application/x-tar', 'application/x-gzip-compressed'],
        'xhtml' => 'application/xhtml+xml',
        'xht'   => 'application/xhtml+xml',
        'zip'   => ['application/x-zip', 'application/zip', 'application/x-zip-compressed'],
        'mid'   => 'audio/midi',
        'midi'  => 'audio/midi',
        'mpga'  => 'audio/mpeg',
        'mp2'   => 'audio/mpeg',
        'mp3'   => ['audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'],
        'm4a'   => 'audio/mp4',
        'aif'   => 'audio/x-aiff',
        'aiff'  => 'audio/x-aiff',
        'aifc'  => 'audio/x-aiff',
        'ram'   => 'audio/x-pn-realaudio',
        'rm'    => 'audio/x-pn-realaudio',
        'rpm'   => 'audio/x-pn-realaudio-plugin',
        'ra'    => 'audio/x-realaudio',
        'rv'    => 'video/vnd.rn-realvideo',
        'wav'   => 'audio/x-wav',
        'bmp'   => 'image/bmp',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'jpg'   => ['image/jpeg', 'image/pjpeg'],
        'jpeg'  => ['image/jpeg', 'image/pjpeg'],
        'jpe'   => ['image/jpeg', 'image/pjpeg'],
        'png'   => 'image/png',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
        'svg'   => 'image/svg+xml',
        'css'   => 'text/css',
        'html'  => 'text/html',
        'htm'   => 'text/html',
        'shtml' => 'text/html',
        'txt'   => 'text/plain',
        'text'  => 'text/plain',
        'log'   => ['text/plain', 'text/x-log'],
        'rtx'   => 'text/richtext',
        'ics'   => 'text/calendar',
        'rtf'   => 'text/rtf',
        'xml'   => 'text/xml',
        'xsl'   => 'text/xml',
        'mpeg'  => 'video/mpeg',
        'mpg'   => 'video/mpeg',
        'mpe'   => 'video/mpeg',
        'mp4'   => 'video/mp4',
        'm4v'   => 'video/mp4',
        'qt'    => 'video/quicktime',
        'mov'   => 'video/quicktime',
        'avi'   => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'xls'   => ['application/excel', 'application/vnd.ms-excel', 'application/msexcel'],
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'ppt'   => ['application/powerpoint', 'application/vnd.ms-powerpoint'],
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'word'  => ['application/msword', 'application/octet-stream'],
        'xl'    => 'application/excel',
        'eml'   => 'message/rfc822',
        'json'  => ['application/json', 'text/json'],
        'odt'   => 'application/vnd.oasis.opendocument.text',
        'odc'   => 'application/vnd.oasis.opendocument.chart',
        'odp'   => 'application/vnd.oasis.opendocument.presentation',
        'webm'  => 'video/webm'
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
