<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;

/**
 * Info sections have a headline and a
 * text. The info boxes can have different
 * themes (positive, negative, info)
 */
class BlueprintInfoSection extends BlueprintSection
{
    protected $headline;
    protected $theme;
    protected $text;

    protected function setHeadline(string $headline)
    {
        $this->headline = $headline;
        return $this;
    }

    protected function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }

    protected function setTheme(string $theme = null)
    {
        $this->theme = $theme;
        return $this;
    }

    public function text()
    {
        return App::instance()->kirbytext($this->model()->toString($this->text));
    }

    public function toArray(): array
    {
        return [
            'code' => 200,
            'data' => [
                'headline' => $this->headline,
                'text'     => $this->text(),
                'theme'    => $this->theme
            ],
            'status' => 'ok',
        ];
    }
}
