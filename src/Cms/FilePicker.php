<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The FilePicker class helps to
 * fetch the right files for the API calls
 * for the file picker component in the panel.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class FilePicker
{
    /**
     * @var \Kirby\Cms\App
     */
    protected $kirby;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Kirby\Cms\Site
     */
    protected $site;

    /**
     * Creates a new FilePicker instance
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        // default params
        $defaults = [
            // image settings (ratio, cover, etc.)
            'image' => [],
            // query template for the file info field
            'info' => false,
            // number of pages displayed per pagination page
            'limit' => 20,
            // optional mapping function for the pages array
            'map' => null,
            // the reference model (site, page, file or user)
            'model' => site(),
            // current page when paginating
            'page' => 1,
            // a query string to fetch specific pages
            'query' => null,
            // search query
            'search' => null,
            // query template for the file text field
            'text' => '{{ file.filename }}'
        ];

        $this->options = array_merge($defaults, $params);
        $this->kirby   = $this->options['model']->kirby();
        $this->site    = $this->kirby->site();
    }


    /**
     * Search all files for the picker
     *
     * @return \Kirby\Cms\Files|null
     */
    public function files()
    {
        $model = $this->options['model'];

        // find the right default query
        if (empty($this->options['query']) === false) {
            $query = $this->options['query'];
        } elseif (is_a($model, 'Kirby\Cms\File') === true) {
            $query = 'file.siblings';
        } else {
            $query = $model::CLASS_ALIAS . '.files';
        }

        // fetch all files for the picker
        $files = $model->query($query);

        // help mitigate some typical query usage issues
        // by converting site and page objects to proper
        // pages by returning their children
        if (is_a($files, 'Kirby\Cms\Site') === true) {
            $files = $files->files();
        } elseif (is_a($files, 'Kirby\Cms\Page') === true) {
            $files = $files->files();
        } elseif (is_a($files, 'Kirby\Cms\User') === true) {
            $files = $files->files();
        } elseif (is_a($files, 'Kirby\Cms\Files') === false) {
            throw new InvalidArgumentException('Your query must return a set of files');
        }


        if (empty($this->options['search']) === false) {
            $files = $files->search($this->options['search']);
        }

        // paginate the result
        $files = $files->paginate([
            'limit' => $this->options['limit'],
            'page'  => $this->options['page']
        ]);

        return $files;
    }

    /**
     * Converts all given files to an associative
     * array that is already optimized for the
     * panel picker component.
     *
     * @param \Kirby\Cms\Files|null $files
     * @return array
     */
    public function filesToArray($files = null): array
    {
        if ($files === null) {
            return [];
        }

        $result = [];

        foreach ($files as $index => $file) {
            if (empty($this->options['map']) === false) {
                $result[] = $this->options['map']($file);
            } else {
                $result[] = $file->panelPickerData([
                    'image' => $this->options['image'],
                    'info'  => $this->options['info'],
                    'model' => $this->options['model'],
                    'text'  => $this->options['text'],
                ]);
            }
        }

        return $result;
    }

    /**
     * Return the most relevant pagination
     * info as array
     *
     * @param \Kirby\Cms\Pagination $pagination
     * @return array
     */
    public function paginationToArray(Pagination $pagination): array
    {
        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total()
        ];
    }

    /**
     * Returns an associative array
     * with all information for the picker.
     * This will be passed directly to the API.
     *
     * @return array
     */
    public function toArray(): array
    {
        $files = $this->files();

        return [
            'data'       => $this->filesToArray($files),
            'pagination' => $this->paginationToArray($files->pagination())
        ];
    }
}
