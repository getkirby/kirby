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
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Body
{
    use Properties;

    /**
     * @var string|null
     */
    protected $html;

    /**
     * @var string|null
     */
    protected $text;

    /**
     * Email body constructor
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->setProperties($props);
    }

    /**
     * Returns body html
     *
     * @return string|null
     */
    public function html()
    {
        return $this->html;
    }

    /**
     * Returns body text
     *
     * @return string|null
     */
    public function text()
    {
        return $this->text;
    }

    /**
     * Sets body as HTML
     *
     * @param string|null $html
     * @return self
     */
    protected function setHtml(string $html = null)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Sets body as plain text
     *
     * @param string|null $text
     * @return self
     */
    protected function setText(string $text = null)
    {
        $this->text = $text;
        return $this;
    }
}
