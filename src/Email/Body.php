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
     * Returns the HTML content of the email body
     *
     * @return string|null
     */
    public function html()
    {
        return $this->html;
    }

    /**
     * Returns the plain text content of the email body
     *
     * @return string|null
     */
    public function text()
    {
        return $this->text;
    }

    /**
     * Sets the HTML content for the email body
     *
     * @param string|null $html
     * @return $this
     */
    protected function setHtml(string $html = null)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Sets the plain text content for the email body
     *
     * @param string|null $text
     * @return $this
     */
    protected function setText(string $text = null)
    {
        $this->text = $text;
        return $this;
    }
}
