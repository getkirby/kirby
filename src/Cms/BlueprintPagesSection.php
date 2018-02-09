<?php

namespace Kirby\Cms;

use Exception;

class BlueprintPagesSection extends BlueprintSection
{

    const ACCEPT = Pages::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected function defaultQuery(): string
    {
        return 'page.children';
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['data'] = $this->result();

        ksort($array);

        return $array;
    }

}
