<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Toolkit\Str;

class Blocks extends Plain
{
    public function fallback(string $html)
    {
        $html = trim($html);

        if (Str::length($html) === 0) {
            return false;
        }

        return [
            'content' => [
                'text' => '<p>' . $html . '</p>',
            ],
            'type' => 'text',
        ];
    }

    public function heading($node, $level)
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

    public function list($node)
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

    public function marks(): array
    {
        return [
            [
                'tag' => 'a',
                'attrs' => ['href', 'target', 'title'],
            ],
            [
                'tag' => 'abbr',
            ],
            [
                'tag' => 'b'
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

    public function nodes(): array
    {
        return [
            [
                'tag' => 'blockquote',
                'parse' => function ($node) {
                    $citation = null;
                    $text     = [];

                    // get all the text for the quote
                    foreach ($node->element()->childNodes as $child) {
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
                'parse' => function ($node) {
                    return $this->heading($node, 'h1');
                }
            ],
            [
                'tag' => 'h2',
                'parse' => function ($node) {
                    return $this->heading($node, 'h2');
                }
            ],
            [
                'tag' => 'h3',
                'parse' => function ($node) {
                    return $this->heading($node, 'h3');
                }
            ],
            [
                'tag' => 'h4',
                'parse' => function ($node) {
                    return $this->heading($node, 'h4');
                }
            ],
            [
                'tag' => 'h5',
                'parse' => function ($node) {
                    return $this->heading($node, 'h5');
                }
            ],
            [
                'tag' => 'h6',
                'parse' => function ($node) {
                    return $this->heading($node, 'h6');
                }
            ],
            [
                'tag' => 'iframe',
                'parse' => function ($node) {
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
                'parse' => function ($node) {
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
                'parse' => function ($node) {
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
                'parse' => function ($node) {
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
                'parse' => function ($node) {
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
                'parse' => function ($node) {
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
