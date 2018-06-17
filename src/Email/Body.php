<?php

namespace Kirby\Email;

use Kirby\Toolkit\Properties;

/**
 * Representation of a an Email body
 * with a text and optional html version
 *
 * @package   Kirby Email
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
*/
class Body
{
    use Properties;

    protected $html;
    protected $text;

    public function __construct(array $props = [])
    {
        $this->setProperties($props);
    }

    public function html()
    {
        return $this->html;
    }

    public function text(): string
    {
        return $this->text;
    }

    protected function setHtml(string $html = null)
    {
        $this->html = $html;
        return $this;
    }

    protected function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }
}
