<?php

namespace Kirby\Cms;

trait HasTranslations
{
    protected $translations;

    protected function setTranslations(array $translations = null)
    {
        if ($translations !== null) {
            $this->translations = new Collection;

            foreach ($translations as $props) {
                $props['parent'] = $this;
                $translation = new ContentTranslation($props);
                $this->translations->data[$translation->code()] = $translation;
            }
        }

        return $this;
    }

    public function translations()
    {
        if ($this->translations !== null) {
            return $this->translations;
        }

        $this->translations = new Collection;

        foreach ($this->kirby()->option('languages', []) as $language) {
            $translation = new ContentTranslation([
                'parent' => $this,
                'code'   => $language['code'],
            ]);

            $this->translations->data[$translation->code()] = $translation;
        }

        return $this->translations;
    }
}
