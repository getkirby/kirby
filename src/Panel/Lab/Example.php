<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;

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
			throw new NotFoundException('The example could not be found');
		}

		$this->tabs = $this->collectTabs();
		$this->tab  = $this->collectTab($tab);
	}

	public function collectTab(string|null $tab): string|null
	{
		if (empty($this->tabs) === true) {
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
		return is_dir($this->root) === true;
	}

	public function file(string $filename): string
	{
		return $this->parent->root() . '/' . $this->path() . '/' . $filename;
	}

	public function github(): string
	{
		$path = Str::after($this->root(), App::instance()->root('kirby'));

		if ($tab = $this->tab()) {
			$path .= '/' . $tab;
		}

		return 'https://github.com/getkirby/kirby/tree/main' . $path;
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
				'back' => 'white',
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
		$parts['template'] = $this->vueTemplate($file);
		$parts['examples'] = $this->vueExamples($parts['template']);
		$parts['script']   = $this->vueScript($file);
		$parts['style']    = $this->vueStyle($file);

		return $parts;
	}

	public function vueExamples(string|null $template): array
	{
		$template ??= '';
		$examples   = [];

		if (preg_match_all('!<k-lab-example[\s|\n].*?label="(.*?)".*?>(.*?)<\/k-lab-example>!s', $template, $matches)) {
			foreach ($matches[1] as $key => $name) {
				$code = $matches[2][$key];

				// only use the code between the @code and @code-end comments
				if (preg_match('$<!-- @code -->(.*?)<!-- @code-end -->$s', $code, $match)) {
					$code = $match[1];
				}

				if (preg_match_all('/^(\t*)\S/m', $code, $indents)) {
					// get minimum indent
					$indents = array_map(fn ($i) => strlen($i), $indents[1]);
					$indents = min($indents);

					// strip minimum indent from each line
					$code = preg_replace('/^\t{' . $indents . '}/m', '', $code);
				}

				$examples[$name] = trim($code);
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
