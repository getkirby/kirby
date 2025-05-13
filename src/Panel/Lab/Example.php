<?php

namespace Kirby\Panel\Lab;

use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

/**
 * One or multiple lab examples with one or multiple tabs
 *
 * @internal
 * @since 4.0.0
 * @codeCoverageIgnore
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Example
{
	protected string $root;
	protected string|null $tab = null;
	protected array $tabs;

	public function __construct(
		protected Category $parent,
		protected string $id,
		string|null $tab = null,
	) {
		$this->root = $this->parent->root() . '/' . $this->id;

		if ($this->exists() === false) {
			throw new NotFoundException(
				message: 'The example could not be found'
			);
		}

		$this->tabs = $this->collectTabs();
		$this->tab  = $this->collectTab($tab);
	}

	public function collectTab(string|null $tab): string|null
	{
		if ($this->tabs === []) {
			return null;
		}

		if (array_key_exists($tab, $this->tabs) === true) {
			return $tab;
		}

		return array_key_first($this->tabs);
	}

	public function collectTabs(): array
	{
		$tabs = [];

		foreach (Dir::inventory($this->root)['children'] as $child) {
			$tabs[$child['dirname']] = [
				'name'  => $child['dirname'],
				'label' => $child['slug'],
				'link'  => '/lab/' . $this->parent->id() . '/' . $this->id . '/' . $child['dirname']
			];
		}

		return $tabs;
	}

	public function exists(): bool
	{
		return Dir::exists($this->root, $this->parent->root()) === true;
	}

	public function file(string $filename): string
	{
		return $this->parent->root() . '/' . $this->path() . '/' . $filename;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function load(string $filename): array|null
	{
		if ($file = $this->file($filename)) {
			return F::load($file);
		}

		return null;
	}

	public function module(): string
	{
		return $this->url() . '/index.vue';
	}

	public function path(): string
	{
		return match ($this->tab) {
			null    => $this->id,
			default => $this->id . '/' . $this->tab
		};
	}

	public function props(): array
	{
		if ($this->tab !== null) {
			$props = $this->load('../index.php');
		}

		return array_replace_recursive(
			$props ?? [],
			$this->load('index.php') ?? []
		);
	}

	public function read(string $filename): string|null
	{
		$file = $this->file($filename);

		if (is_file($file) === false) {
			return null;
		}

		return F::read($file);
	}

	public function root(): string
	{
		return $this->root;
	}

	public function serve(): Response
	{
		return new Response($this->vue()['script'], 'application/javascript');
	}

	public function tab(): string|null
	{
		return $this->tab;
	}

	public function tabs(): array
	{
		return $this->tabs;
	}

	public function template(string $filename): string|null
	{
		$file = $this->file($filename);

		if (is_file($file) === false) {
			return null;
		}

		$data = $this->props();
		return (new Template($file))->render($data);
	}

	public function title(): string
	{
		return basename($this->id);
	}

	public function toArray(): array
	{
		return [
			'image' => [
				'icon' => $this->parent->icon(),
				'back' => 'light-dark(white, var(--color-gray-800))',
			],
			'text' => $this->title(),
			'link' => $this->url()
		];
	}

	public function url(): string
	{
		return '/lab/' . $this->parent->id() . '/' . $this->path();
	}

	public function vue(): array
	{
		// read the index.vue file (or programmabel Vue PHP file)
		$file   = $this->read('index.vue');
		$file ??= $this->template('index.vue.php');
		$file ??= '';

		// extract parts
		$parts['script']   = $this->vueScript($file);
		$parts['template'] = $this->vueTemplate($file);
		$parts['examples'] = $this->vueExamples($parts['template'], $parts['script']);
		$parts['style']    = $this->vueStyle($file);

		return $parts;
	}

	public function vueExamples(string|null $template, string|null $script): array
	{
		$template ??= '';
		$examples   = [];
		$scripts    = [];

		if (preg_match_all('!\/\*\* \@script: (.*?)\*\/(.*?)\/\*\* \@script-end \*\/!s', $script, $matches)) {
			foreach ($matches[1] as $key => $name) {
				$code = $matches[2][$key];
				$code = preg_replace('!const (.*?) \=!', 'default', $code);

				$scripts[trim($name)] = $code;
			}
		}

		if (preg_match_all('!<k-lab-example[\s|\n].*?label="(.*?)"(.*?)>(.*?)<\/k-lab-example>!s', $template, $matches)) {
			foreach ($matches[1] as $key => $name) {
				$tail = $matches[2][$key];
				$code = $matches[3][$key];

				$scriptId = trim(preg_replace_callback(
					'!script="(.*?)"!',
					fn ($match) => trim($match[1]),
					$tail
				));

				$scriptBlock = $scripts[$scriptId] ?? null;

				if (empty($scriptBlock) === false) {
					$js  = PHP_EOL . PHP_EOL;
					$js .= '<script>';
					$js .= $scriptBlock;
					$js .= '</script>';
				} else {
					$js = '';
				}

				// only use the code between the @code and @code-end comments
				if (preg_match('$<!-- @code -->(.*?)<!-- @code-end -->$s', $code, $match)) {
					$code = $match[1];
				}

				if (preg_match_all('/^(\t*)\S/m', $code, $indents)) {
					// get minimum indent
					$indents = array_map(fn ($i) => strlen($i), $indents[1]);
					$indents = min($indents);

					if (empty($js) === false) {
						$indents--;
					}

					// strip minimum indent from each line
					$code = preg_replace('/^\t{' . $indents . '}/m', '', $code);
				}

				$code = trim($code);

				if (empty($js) === false) {
					$code = '<template>' . PHP_EOL . "\t" . $code . PHP_EOL . '</template>';
				}

				$examples[$name] = $code . $js;
			}
		}

		return $examples;
	}

	public function vueScript(string $file): string
	{
		if (preg_match('!<script>(.*)</script>!s', $file, $match)) {
			return trim($match[1]);
		}

		return 'export default {}';
	}

	public function vueStyle(string $file): string|null
	{
		if (preg_match('!<style>(.*)</style>!s', $file, $match)) {
			return trim($match[1]);
		}

		return null;
	}

	public function vueTemplate(string $file): string|null
	{
		if (preg_match('!<template>(.*)</template>!s', $file, $match)) {
			return preg_replace('!^\n!', '', $match[1]);
		}

		return null;
	}
}
