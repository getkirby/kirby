<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Toolkit\Str;

/**
 * The plain schema definition converts
 * the entire document into simple text blocks
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Blocks extends Plain
{
    /**
     * Creates the fallback block type
     * if no other block can be found
     *
     * @param string $html
     * @return array|null
     */
    public function fallback(string $html): ?array
    {
        $html = trim($html);

        if (Str::length($html) === 0) {
            return null;
        }

        return [
            'content' => [
                'text' => '<p>' . $html . '</p>',
            ],
            'type' => 'text',
        ];
    }

    /**
     * Converts a heading element to a heading block
     *
     * @param \Kirby\Parsley\Element $node
     * @return array
     */
    public function heading(Element $node, $level): array
    {
        $content = [
            'level' => $level,
            'text'  => $node->innerHTML()
        ];

        if ($id = $node->attr('id')) {
            $content['id'] = $id;
        }

        ksort($content);

        return [
            'content' => $content,
            'type'    => 'heading',
        ];
    }

    /**
     * Converts a list element to HTML
     *
     * @param \Kirby\Parsley\Element $node
     * @return string
     */
    public function list(Element $node): string
    {
        $html = [];

        foreach ($node->filter('li') as $li) {
            $innerHtml = '';

            foreach ($li->children() as $child) {
                if (is_a($child, 'DOMText') === true) {
                    $innerHtml .= $child->textContent;
                } elseif (is_a($child, 'DOMElement') === true) {
                    $child = new Element($child);

                    if (in_array($child->tagName(), ['ul', 'ol']) === true) {
                        $innerHtml .= $this->list($child);
                    } else {
                        $innerHtml .= $child->innerHTML($this->marks());
                    }
                }
            }

            $html[] = '<li>' . trim($innerHtml) . '</li>';
        }

        return '<' . $node->tagName() . '>' . implode($html) . '</' . $node->tagName() . '>';
    }

    /**
     * Returns a list of allowed inline marks
     * and their parsing rules
     *
     * @return array
     */
    public function marks(): array
    {
        return [
            [
                'tag' => 'a',
                'attrs' => ['href', 'rel', 'target', 'title'],
                'defaults' => [
                    'rel' => 'noopener noreferrer'
                ]
            ],
            [
                'tag' => 'abbr',
            ],
            [
                'tag' => 'b'
            ],
            [
                'tag' => 'br',
            ],
            [
                'tag' => 'code'
            ],
            [
                'tag' => 'del',
            ],
            [
                'tag' => 'em',
            ],
            [
                'tag' => 'i',
            ],
            [
                'tag' => 'strike',
            ],
            [
                'tag' => 'sub',
            ],
            [
                'tag' => 'sup',
            ],
            [
                'tag' => 'strong',
            ],
            [
                'tag' => 'u',
            ],
        ];
    }

    /**
     * Returns a list of allowed nodes and
     * their parsing rules
     *
     * @return array
     */
    public function nodes(): array
    {
        return [
            [
                'tag' => 'blockquote',
                'parse' => function (Element $node) {
                    $citation = null;
                    $text     = [];

                    // get all the text for the quote
                    foreach ($node->children() as $child) {
                        if (is_a($child, 'DOMText') === true) {
                            $text[] = trim($child->textContent);
                        }
                        if (is_a($child, 'DOMElement') === true && $child->tagName !== 'footer') {
                            $text[] = (new Element($child))->innerHTML($this->marks());
                        }
                    }

                    // filter empty blocks and separate text blocks with breaks
                    $text = implode('<br></br>', array_filter($text));

                    // get the citation from the footer
                    if ($footer = $node->find('footer')) {
                        $citation = $footer->innerHTML($this->marks());
                    }

                    return [
                        'content' => [
                            'citation' => $citation,
                            'text'     => $text
                        ],
                        'type' => 'quote',
                    ];
                }
            ],
            [
                'tag' => 'h1',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h1');
                }
            ],
            [
                'tag' => 'h2',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h2');
                }
            ],
            [
                'tag' => 'h3',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h3');
                }
            ],
            [
                'tag' => 'h4',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h4');
                }
            ],
            [
                'tag' => 'h5',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h5');
                }
            ],
            [
                'tag' => 'h6',
                'parse' => function (Element $node) {
                    return $this->heading($node, 'h6');
                }
            ],
            [
                'tag' => 'hr',
                'parse' => function (Element $node) {
                    return [
                        'type' => 'line'
                    ];
                }
            ],
            [
                'tag' => 'iframe',
                'parse' => function (Element $node) {
                    $caption = null;
                    $src     = $node->attr('src');

                    if ($figcaption = $node->find('ancestor::figure[1]//figcaption')) {
                        $caption = $figcaption->innerHTML($this->marks());

                        // avoid parsing the caption twice
                        $figcaption->remove();
                    }

                    // reverse engineer video URLs
                    if (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $src, $array) === 1) {
                        $src = 'https://vimeo.com/' . $array[1];
                    } elseif (preg_match('!youtube.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
                        $src = 'https://youtube.com/watch?v=' . $array[1];
                    } elseif (preg_match('!youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
                        $src = 'https://youtube.com/watch?v=' . $array[1];
                    } else {
                        $src = false;
                    }

                    // correct video URL
                    if ($src) {
                        return [
                            'content' => [
                                'caption' => $caption,
                                'url'     => $src
                            ],
                            'type' => 'video',
                        ];
                    }

                    return [
                        'content' => [
                            'text' => $node->outerHTML()
                        ],
                        'type' => 'markdown',
                    ];
                }
            ],
            [
                'tag' => 'img',
                'parse' => function (Element $node) {
                    $caption = null;
                    $link = null;

                    if ($figcaption = $node->find('ancestor::figure[1]//figcaption')) {
                        $caption = $figcaption->innerHTML($this->marks());

                        // avoid parsing the caption twice
                        $figcaption->remove();
                    }

                    if ($a = $node->find('ancestor::a')) {
                        $link = $a->attr('href');
                    }

                    return [
                        'content' => [
                            'alt'      => $node->attr('alt'),
                            'caption'  => $caption,
                            'link'     => $link,
                            'location' => 'web',
                            'src'      => $node->attr('src'),
                        ],
                        'type' => 'image',
                    ];
                }
            ],
            [
                'tag' => 'ol',
                'parse' => function (Element $node) {
                    return [
                        'content' => [
                            'text' => $this->list($node)
                        ],
                        'type' => 'list',
                    ];
                }
            ],
            [
                'tag'   => 'pre',
                'parse' => function (Element $node) {
                    $language = 'text';

                    if ($code = $node->find('//code')) {
                        foreach ($code->classList() as $className) {
                            if (preg_match('!language-(.*?)!', $className)) {
                                $language = str_replace('language-', '', $className);
                                break;
                            }
                        }
                    }

                    return [
                        'content' => [
                            'code'     => $node->innerText(),
                            'language' => $language
                        ],
                        'type' => 'code',
                    ];
                }
            ],
            [
                'tag' => 'table',
                'parse' => function (Element $node) {
                    return [
                        'content' => [
                            'text' => $node->outerHTML(),
                        ],
                        'type' => 'markdown',
                    ];
                }
            ],
            [
                'tag' => 'ul',
                'parse' => function (Element $node) {
                    return [
                        'content' => [
                            'text' => $this->list($node)
                        ],
                        'type' => 'list',
                    ];
                }
            ],
        ];
    }
}
