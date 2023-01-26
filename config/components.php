<?php

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\FileVersion;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Email\PHPMailer as Emailer;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Filename;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Image\Darkroom;
use Kirby\Template\Snippet;
use Kirby\Template\Template;
use Kirby\Text\Markdown;
use Kirby\Text\SmartyPants;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [

	/**
	 * Used by the `css()` helper
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $url Relative or absolute URL
	 * @param string|array $options An array of attributes for the link tag or a media attribute string
	 */
	'css' => fn (App $kirby, string $url, $options = null): string => $url,

	/**
	 * Add your own email provider
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param array $props
	 * @param bool $debug
	 */
	'email' => function (App $kirby, array $props = [], bool $debug = false) {
		return new Emailer($props, $debug);
	},

	/**
	 * Modify URLs for file objects
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param \Kirby\Cms\File $file The original file object
	 * @return string
	 */
	'file::url' => function (App $kirby, File $file): string {
		return $file->mediaUrl();
	},

	/**
	 * Adapt file characteristics
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param \Kirby\Cms\File|\Kirby\Filesystem\Asset $file The file object
	 * @param array $options All thumb options (width, height, crop, blur, grayscale)
	 * @return \Kirby\Cms\File|\Kirby\Cms\FileVersion|\Kirby\Filesystem\Asset
	 */
	'file::version' => function (App $kirby, $file, array $options = []) {
		// if file is not resizable, return
		if ($file->isResizable() === false) {
			return $file;
		}

		// create url and root
		$mediaRoot = dirname($file->mediaRoot());
		$template  = $mediaRoot . '/{{ name }}{{ attributes }}.{{ extension }}';
		$thumbRoot = (new Filename($file->root(), $template, $options))->toString();
		$thumbName = basename($thumbRoot);

		// check if the thumb already exists
		if (file_exists($thumbRoot) === false) {
			// if not, create job file
			$job = $mediaRoot . '/.jobs/' . $thumbName . '.json';

			try {
				Data::write($job, array_merge($options, [
					'filename' => $file->filename()
				]));
			} catch (Throwable) {
				// if thumb doesn't exist yet and job file cannot
				// be created, return
				return $file;
			}
		}

		return new FileVersion([
			'modifications' => $options,
			'original'      => $file,
			'root'          => $thumbRoot,
			'url'           => dirname($file->mediaUrl()) . '/' . $thumbName,
		]);
	},

	/**
	 * Used by the `js()` helper
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $url Relative or absolute URL
	 * @param string|array $options An array of attributes for the link tag or a media attribute string
	 */
	'js' => fn (App $kirby, string $url, $options = null): string => $url,

	/**
	 * Add your own Markdown parser
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $text Text to parse
	 * @param array $options Markdown options
	 * @return string
	 */
	'markdown' => function (
		App $kirby,
		string $text = null,
		array $options = []
	): string {
		static $markdown;
		static $config;

		// if the config options have changed or the component is called for the first time,
		// (re-)initialize the parser object
		if ($config !== $options) {
			$markdown = new Markdown($options);
			$config   = $options;
		}

		return $markdown->parse($text, $options['inline'] ?? false);
	},

	/**
	 * Add your own search engine
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param \Kirby\Cms\Collection $collection Collection of searchable models
	 * @param string $query
	 * @param mixed $params
	 * @return \Kirby\Cms\Collection|bool
	 */
	'search' => function (App $kirby, Collection $collection, string $query = null, $params = []) {
		// empty search query
		if (empty(trim($query ?? '')) === true) {
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

		$words = preg_replace('/(\s)/u', ',', $query);
		$words = Str::split($words, ',', $options['minlength']);
		$exact = $options['words'] ? '(\b' . preg_quote($query) . '\b)' : preg_quote($query);
		$query = Str::lower($query);

		if (empty($options['stopwords']) === false) {
			$words = array_diff($words, $options['stopwords']);
		}

		$words = A::map(
			$words,
			fn ($value) => $options['words'] ? '\b' . preg_quote($value) . '\b' : preg_quote($value)
		);

		// returns an empty collection if there is no search word
		if (empty($words) === true) {
			return $collection->limit(0);
		}

		$preg    = '!(' . implode('|', $words) . ')!i';
		$scores  = [];
		$results = $collection->filter(function ($item) use ($query, $exact, $preg, $options, &$scores) {
			$data   = $item->content()->toArray();
			$keys   = array_keys($data);
			$keys[] = 'id';

			if ($item instanceof User) {
				$keys[] = 'name';
				$keys[] = 'email';
				$keys[] = 'role';
			} elseif ($item instanceof Page) {
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

			$scoring = [
				'hits'  => 0,
				'score' => 0
			];

			foreach ($keys as $key) {
				$score = $options['score'][$key] ?? 1;
				$value = $data[$key] ?? (string)$item->$key();

				$lowerValue = Str::lower($value);

				// check for exact matches
				if ($query == $lowerValue) {
					$scoring['score'] += 16 * $score;
					$scoring['hits']  += 1;

				// check for exact beginning matches
				} elseif (
					$options['words'] === false &&
					Str::startsWith($lowerValue, $query) === true
				) {
					$scoring['score'] += 8 * $score;
					$scoring['hits']  += 1;

				// check for exact query matches
				} elseif ($matches = preg_match_all('!' . $exact . '!i', $value, $r)) {
					$scoring['score'] += 2 * $score;
					$scoring['hits']  += $matches;
				}

				// check for any match
				if ($matches = preg_match_all($preg, $value, $r)) {
					$scoring['score'] += $matches * $score;
					$scoring['hits']  += $matches;
				}
			}

			$scores[$item->id()] = $scoring;
			return $scoring['hits'] > 0;
		});

		return $results->sort(
			fn ($item) => $scores[$item->id()]['score'],
			'desc'
		);
	},

	/**
	 * Add your own SmartyPants parser
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $text Text to parse
	 * @param array $options SmartyPants options
	 * @return string
	 */
	'smartypants' => function (App $kirby, string $text = null, array $options = []): string {
		static $smartypants;
		static $config;

		// if the config options have changed or the component is called for the first time,
		// (re-)initialize the parser object
		if ($config !== $options) {
			$smartypants = new Smartypants($options);
			$config      = $options;
		}

		return $smartypants->parse($text);
	},

	/**
	 * Add your own snippet loader
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string|array $name Snippet name
	 * @param array $data Data array for the snippet
	 */
	'snippet' => function (App $kirby, string|array|null $name, array $data = [], bool $slots = false): Snippet|string {
		return Snippet::factory($name, $data, $slots);
	},

	/**
	 * Add your own template engine
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $name Template name
	 * @param string $type Extension type
	 * @param string $defaultType Default extension type
	 * @return \Kirby\Template\Template
	 */
	'template' => function (App $kirby, string $name, string $type = 'html', string $defaultType = 'html') {
		return new Template($name, $type, $defaultType);
	},

	/**
	 * Add your own thumb generator
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string $src Root of the original file
	 * @param string $dst Template string for the root to the desired destination
	 * @param array $options All thumb options that should be applied: `width`, `height`, `crop`, `blur`, `grayscale`
	 * @return string
	 */
	'thumb' => function (App $kirby, string $src, string $dst, array $options): string {
		$darkroom = Darkroom::factory(
			$kirby->option('thumbs.driver', 'gd'),
			$kirby->option('thumbs', [])
		);
		$options  = $darkroom->preprocess($src, $options);
		$root     = (new Filename($src, $dst, $options))->toString();

		F::copy($src, $root, true);
		$darkroom->process($root, $options);

		return $root;
	},

	/**
	 * Modify all URLs
	 *
	 * @param \Kirby\Cms\App $kirby Kirby instance
	 * @param string|null $path URL path
	 * @param array|string|null $options Array of options for the Uri class
	 * @return string
	 */
	'url' => function (App $kirby, string $path = null, $options = null): string {
		$language = null;

		// get language from simple string option
		if (is_string($options) === true) {
			$language = $options;
			$options  = null;
		}

		// get language from array
		if (is_array($options) === true && isset($options['language']) === true) {
			$language = $options['language'];
			unset($options['language']);
		}

		// get a language url for the linked page, if the page can be found
		if ($kirby->multilang() === true) {
			$parts = Str::split($path, '#');

			if ($parts[0] ?? null) {
				$page = $kirby->site()->find($parts[0]);
			} else {
				$page = $kirby->site()->page();
			}

			if ($page) {
				$path = $page->url($language);

				if (isset($parts[1]) === true) {
					$path .= '#' . $parts[1];
				}
			}
		}

		// keep relative urls
		if (
			$path !== null &&
			(substr($path, 0, 2) === './' || substr($path, 0, 3) === '../')
		) {
			return $path;
		}

		$url = Url::makeAbsolute($path, $kirby->url());

		if ($options === null) {
			return $url;
		}

		return (new Uri($url, $options))->toString();
	},

];
