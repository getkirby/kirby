<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;

class LanguageRoutes
{
    /**
     * Creates all multi-language routes
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function create(App $kirby): array
    {
        $routes = [];

        // add the route for the home page
        $routes[] = static::home($kirby);

        // Kirby's base url
        $baseurl = $kirby->url();

        foreach ($kirby->languages() as $language) {

            // ignore languages with a different base url
            if ($language->baseurl() !== $baseurl) {
                continue;
            }

            $routes[] = [
                'pattern' => $language->pattern(),
                'method'  => 'ALL',
                'env'     => 'site',
                'action'  => function ($path = null) use ($language) {
                    if ($result = $language->router()->call($path)) {
                        return $result;
                    }

                    // jump through to the fallback if nothing
                    // can be found for this language
                    $this->next();
                }
            ];
        }

        $routes[] = static::fallback($kirby);

        return $routes;
    }


    /**
     * Create the fallback route
     * for unprefixed default language URLs.
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function fallback(App $kirby): array
    {
        return [
            'pattern' => '(:all)',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function (string $path) use ($kirby) {

                // check for content representations or files
                $extension = F::extension($path);

                // try to redirect prefixed pages
                if (empty($extension) === true && $page = $kirby->page($path)) {
                    $url = $kirby->request()->url([
                        'query'    => null,
                        'params'   => null,
                        'fragment' => null
                    ]);

                    if ($url->toString() !== $page->url()) {
                        return $kirby
                            ->response()
                            ->redirect($page->url());
                    }
                }

                return $kirby->defaultLanguage()->router()->call($path);
            }
        ];
    }

    /**
     * Create the multi-language home page route
     *
     * @param \Kirby\Cms\App $kirby
     * @return array
     */
    public static function home(App $kirby): array
    {
        // Multi-language home
        return [
            'pattern' => '',
            'method'  => 'ALL',
            'env'     => 'site',
            'action'  => function () use ($kirby) {

                // find all languages with the same base url as the current installation
                $languages = $kirby->languages()->filterBy('baseurl', $kirby->url());

                // if there's no language with a matching base url,
                // redirect to the default language
                if ($languages->count() === 0) {
                    return $kirby
                        ->response()
                        ->redirect($kirby->defaultLanguage()->url());
                }

                // if there's just one language, we take that to render the home page
                if ($languages->count() === 1) {
                    $currentLanguage = $languages->first();
                } else {
                    $currentLanguage = $kirby->defaultLanguage();
                }

                // language detection on the home page with / as URL
                if ($kirby->url() !== $currentLanguage->url()) {
                    if ($kirby->option('languages.detect') === true) {
                        return $kirby
                            ->response()
                            ->redirect($kirby->detectedLanguage()->url());
                    }

                    return $kirby
                        ->response()
                        ->redirect($currentLanguage->url());
                }

                // render the home page of the current language
                return $currentLanguage->router()->call();
            }
        ];
    }
}
