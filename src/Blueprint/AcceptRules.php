<?php

namespace Kirby\Blueprint;

/**
 * The AcceptRules class goes through all blueprint settings for
 * sections and fields and collects rules for accepted files
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class AcceptRules
{
	protected array|null $fileTemplates = null;

	public function __construct(
		protected Blueprint $blueprint,
	) {
	}

	/**
	 * Gathers what file templates are allowed in
	 * this model based on the blueprint
	 */
	public function fileTemplates(string|null $inSection = null): array
	{
		// get cached results for the current file model
		// (except when collecting for a specific section)
		if ($inSection === null && $this->fileTemplates !== null) {
			return $this->fileTemplates; // @codeCoverageIgnore
		}

		$templates = [];

		// collect all allowed file templates from blueprint…
		foreach ($this->blueprint->sections() as $section) {
			// if collecting for a specific section, skip all others
			if ($inSection !== null && $section->name() !== $inSection) {
				continue;
			}

			$templates = match ($section->type()) {
				'files'  => [...$templates, $section->template() ?? 'default'],
				'fields' => [
					...$templates,
					...$this->fileTemplatesFromFields($section->fields())
				],
				default  => $templates
			};
		}

		// no caching for when collecting for specific section
		if ($inSection !== null) {
			return $templates; // @codeCoverageIgnore
		}

		return $this->fileTemplates = $templates;
	}

	/**
	 * Gathers the allowed file templates from model's fields
	 */
	public function fileTemplatesFromFields(array $fields): array
	{
		$templates = [];

		foreach ($fields as $field) {
			// fields with uploads settings
			if (isset($field['uploads']) === true && is_array($field['uploads']) === true) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFieldUploads($field['uploads'])
				];
				continue;
			}

			// structure and object fields
			if (isset($field['fields']) === true && is_array($field['fields']) === true) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFields($field['fields']),
				];
				continue;
			}

			// layout and blocks fields
			if (isset($field['fieldsets']) === true && is_array($field['fieldsets']) === true) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFieldsets($field['fieldsets'])
				];
				continue;
			}
		}

		return $templates;
	}

	/**
	 * Gathers the allowed file templates from fieldsets
	 */
	public function fileTemplatesFromFieldsets(array $fieldsets): array
	{
		$templates = [];

		foreach ($fieldsets as $fieldset) {
			foreach (($fieldset['tabs'] ?? []) as $tab) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFields($tab['fields'] ?? [])
				];
			}
		}

		return $templates;
	}

	/**
	 * Extracts templates from field uploads settings
	 */
	public function fileTemplatesFromFieldUploads(array $uploads): array
	{
		// only if the `uploads` parent is this model
		if ($target = $uploads['parent'] ?? null) {
			if ($this->blueprint->model()->id() !== $target) {
				return [];
			}
		}

		return [($uploads['template'] ?? 'default')];
	}
}
