<?php

namespace Kirby\Panel;

use Throwable;

/**
 * Provides information about the file model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class File extends Model
{
    /**
     * Provides a kirbytag or markdown
     * tag for the file, which will be
     * used in the panel, when the file
     * gets dragged onto a textarea
     *
     * @internal
     * @param string|null $type (null|auto|kirbytext|markdown)
     * @param bool $absolute
     * @return string
     */
    public function dragText(string $type = null, bool $absolute = false): string
    {
        $type = $this->dragTextType($type);
        $url  = $absolute ? $this->model->id() : $this->model->filename();

        if ($dragTextFromCallback = $this->dragTextFromCallback($type, $url)) {
            return $dragTextFromCallback;
        }

        if ($type === 'markdown') {
            if ($this->model->type() === 'image') {
                return '![' . $this->model->alt() . '](' . $url . ')';
            }

            return '[' . $this->model->filename() . '](' . $url . ')';
        }

        if ($this->model->type() === 'image') {
            return '(image: ' . $url . ')';
        }
        if ($this->model->type() === 'video') {
            return '(video: ' . $url . ')';
        }

        return '(file: ' . $url . ')';
    }


    /**
     * Panel icon definition
     *
     * @param array|null $params
     * @return array
     */
    public function icon(array $params = null): array
    {
        $colorBlue   = '#81a2be';
        $colorPurple = '#b294bb';
        $colorOrange = '#de935f';
        $colorGreen  = '#a7bd68';
        $colorAqua   = '#8abeb7';
        $colorYellow = '#f0c674';
        $colorRed    = '#d16464';
        $colorWhite  = '#c5c9c6';

        $types = [
            'image'    => ['color' => $colorOrange, 'type' => 'file-image'],
            'video'    => ['color' => $colorYellow, 'type' => 'file-video'],
            'document' => ['color' => $colorRed, 'type' => 'file-document'],
            'audio'    => ['color' => $colorAqua, 'type' => 'file-audio'],
            'code'     => ['color' => $colorBlue, 'type' => 'file-code'],
            'archive'  => ['color' => $colorWhite, 'type' => 'file-zip'],
        ];

        $extensions = [
            'indd'  => ['color' => $colorPurple],
            'xls'   => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'xlsx'  => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'csv'   => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'docx'  => ['color' => $colorBlue, 'type' => 'file-word'],
            'doc'   => ['color' => $colorBlue, 'type' => 'file-word'],
            'rtf'   => ['color' => $colorBlue, 'type' => 'file-word'],
            'mdown' => ['type' => 'file-text'],
            'md'    => ['type' => 'file-text']
        ];

        $definition = array_merge(
            $types[$this->model->type()] ?? [],
            $extensions[$this->model->extension()] ?? []
        );

        $params['type']  = $definition['type']  ?? 'file';
        $params['color'] = $definition['color'] ?? $colorWhite;

        return parent::icon($params);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Cms\Asset|null
     */
    protected function imageSource(string $query = null)
    {
        if ($query === null && $this->model->isViewable()) {
            return $this->model;
        }

        return parent::imageSource($query);
    }

    /**
     * Returns an array of all actions
     * that can be performed in the Panel
     *
     * @param array $unlock An array of options that will be force-unlocked
     * @return array
     */
    public function options(array $unlock = []): array
    {
        $options = parent::options($unlock);

        try {
            // check if the file type is allowed at all,
            // otherwise it cannot be replaced
            $this->model->match($this->model->blueprint()->accept());
        } catch (Throwable $e) {
            $options['replace'] = false;
        }

        return $options;
    }

    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    public function path(): string
    {
        return 'files/' . $this->model->filename();
    }

    /**
     * Prepares the response data for file pickers
     * and file fields
     *
     * @param array|null $params
     * @return array
     */
    public function pickerData(array $params = []): array
    {
        $id   = $this->model->id();
        $name = $this->model->filename();

        if (empty($params['model']) === false) {
            $parent   = $this->model->parent();
            $uuid     = $parent === $params['model'] ? $name : $id;
            $absolute = $parent !== $params['model'];
        }

        return [
            'filename' => $name,
            'dragText' => $this->dragText('auto', $absolute ?? false),
            'id'       => $id,
            'image'    => $image = $this->image($params['image'] ?? []),
            'icon'     => $this->icon($image),
            'info'     => $this->model->toString($params['info'] ?? false),
            'link'     => $this->url(true),
            'text'     => $this->model->toString($params['text'] ?? '{{ file.filename }}'),
            'type'     => $this->model->type(),
            'url'      => $this->model->url(),
            'uuid'     => $uuid ?? $id,
        ];
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @param bool $relative
     * @return string
     */
    public function url(bool $relative = false): string
    {
        $parent = $this->model->parent()->panel()->url($relative);
        return $parent . '/' . $this->path();
    }
}
