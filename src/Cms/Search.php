<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

/**
 * The Search class extracts the
 * search logic from collections, to
 * provide a more globally usable interface
 * for any searches.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Search
{
    /**
     * @param string $query
     * @param array $params
     * @return \Kirby\Cms\Files
     */
    public static function files(string $query = null, $params = [])
    {
        return App::instance()->site()->index()->files()->search($query, $params);
    }

    /**
     * Native search method to search for anything within the collection
     *
     * @param Collection $collection
     * @param string $query
     * @param mixed $params
     */
    public static function collection(Collection $collection, string $query = null, $params = [])
    {
        if (empty(trim($query)) === true) {
            return $collection->limit(0);
        }

        if (is_string($params) === true) {
            $params = ['fields' => Str::split($params, '|')];
        }

        $defaults = [
            'fields'    => [],
            'minlength' => 2,
            'score'     => [],
            'words'     => false,
        ];

        $options     = array_merge($defaults, $params);
        $collection  = clone $collection;
        $searchwords = preg_replace('/(\s)/u', ',', $query);
        $searchwords = Str::split($searchwords, ',', $options['minlength']);
        $lowerQuery  = strtolower($query);

        if (empty($options['stopwords']) === false) {
            $searchwords = array_diff($searchwords, $options['stopwords']);
        }

        $searchwords = array_map(function ($value) use ($options) {
            return $options['words'] ? '\b' . preg_quote($value) . '\b' : preg_quote($value);
        }, $searchwords);

        $preg    = '!(' . implode('|', $searchwords) . ')!i';
        $results = $collection->filter(function ($item) use ($query, $preg, $options, $lowerQuery) {
            $data = $item->content()->toArray();
            $keys = array_keys($data);
            $keys[] = 'id';

            if (is_a($item, 'Kirby\Cms\User') === true) {
                $keys[] = 'email';
                $keys[] = 'role';
            } elseif (is_a($item, 'Kirby\Cms\Page') === true) {
                // apply the default score for pages
                $options['score'] = array_merge([
                    'id'    => 64,
                    'title' => 64,
                ], $options['score']);
            }

            if (empty($options['fields']) === false) {
                $fields = array_map('strtolower', $options['fields']);
                $keys   = array_intersect($keys, $fields);
            }

            $item->searchHits  = 0;
            $item->searchScore = 0;

            foreach ($keys as $key) {
                $score = $options['score'][$key] ?? 1;
                $value = $data[$key] ?? (string)$item->$key();

                $lowerValue = strtolower($value);

                // check for exact matches
                if ($lowerQuery == $lowerValue) {
                    $item->searchScore += 16 * $score;
                    $item->searchHits  += 1;

                // check for exact beginning matches
                } elseif (Str::startsWith($lowerValue, $lowerQuery) === true) {
                    $item->searchScore += 8 * $score;
                    $item->searchHits  += 1;

                // check for exact query matches
                } elseif ($matches = preg_match_all('!' . preg_quote($query) . '!i', $value, $r)) {
                    $item->searchScore += 2 * $score;
                    $item->searchHits  += $matches;
                }

                // check for any match
                if ($matches = preg_match_all($preg, $value, $r)) {
                    $item->searchHits  += $matches;
                    $item->searchScore += $matches * $score;
                }
            }

            return $item->searchHits > 0 ? true : false;
        });

        return $results->sortBy('searchScore', 'desc');
    }

    /**
     * @param string $query
     * @param array $params
     * @return \Kirby\Cms\Pages
     */
    public static function pages(string $query = null, $params = [])
    {
        return App::instance()->site()->index()->search($query, $params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return \Kirby\Cms\Users
     */
    public static function users(string $query = null, $params = [])
    {
        return App::instance()->users()->search($query, $params);
    }
}
