<?php

namespace Kirby\Panel\Lab;

use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;

class Example
{
	protected string $root;
	protected string|null $tab = null;
	protected array $tabs;

	public function __construct(
		protected Examples $parent,
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

		foreach(Dir::inventory($this->root)['children'] as $child) {
			$tabs[$child['dirname']] = [
				'name'  => $child['dirname'],
				'label' => $child['slug'],
				'link'  => '/lab/' .  $this->parent->id() . '/' . $this->id . '/' . $child['dirname']
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

	public function id(): string
	{
		return $this->id;
	}

	public function load(string $filename): array|null
	{
		$file = $this->file($filename);

		if (is_file($file) === false) {
			return null;
		}

		return F::load($file);
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
		if ($file = $this->file($filename)) {
			$data = $this->props();
			return (new Template($file))->render($data);
		}

		return null;
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

		// extract template
		if (preg_match('!<template>(.*)</template>!s', $file, $match)) {
			$parts['template'] = preg_replace('!^\n!', '', $match[1]);
		} else {
			$parts['template'] = null;
		}

		// extract code for each example
		if (preg_match_all('!<k-lab-example.*?label="(.*?)".*?>(.*?)<\/k-lab-example>!s', $parts['template'] ?? '', $matches)) {
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

				$parts['examples'][$name] = trim($code);
			}
		} else {
			$parts['examples'] = [];
		}

		// extract script
		if (preg_match('!<script>(.*)</script>!s', $file, $match)) {
			$parts['script'] = trim($match[1]);
		} else {
			$parts['script'] = 'export default {}';
		}

		// extract style
		if (preg_match('!<style>(.*)</style>!s', $file, $match)) {
			$parts['style'] = trim($match[1]);
		} else {
			$parts['style'] = null;
		}

		return $parts;
	}

}
