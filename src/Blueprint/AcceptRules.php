<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Form\Field;
use Kirby\Form\Mixin\Upload;

/**
 * The AcceptRules class goes through all blueprint settings for
 * sections and fields and collects rules for accepted files
 *
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
	public function fileTemplates(string|null $inField = null): array
	{
		// get cached results for the current file model
		// (except when collecting for a specific field)
		if ($inField === null && $this->fileTemplates !== null) {
			return $this->fileTemplates; // @codeCoverageIgnore
		}

		// collect all allowed file templates from blueprint…
		$fields = match ($inField) {
			null    => $this->blueprint->fields(),
			default => array_filter([$this->blueprint->field($inField)])
		};

		$templates = $this->fileTemplatesFromFields($fields);

		// no caching for when collecting for a specific field
		if ($inField !== null) {
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
			// file lists accept uploads for their own template
			if (($field['type'] ?? null) === 'filelist') {
				$template  = $field['template'] ?? null;
				$templates = [
					...$templates,
					...($template
						? [$template]
						: App::instance()->blueprints('files'))
				];
				continue;
			}

			// fields that support uploads. The blueprint props are not
			// normalized yet, so the string shortcut and the default
			// settings have to be resolved here as well
			$uploads = $field['uploads'] ?? null;

			if ($uploads !== false && $this->supportsUploads($field['type'] ?? null) === true) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFieldUploads(match (true) {
						is_string($uploads) => ['template' => $uploads],
						is_array($uploads)  => $uploads,
						default             => []
					})
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
			// the blueprint props are not normalized yet, so a fieldset
			// can still use the `fields` shortcut instead of tabs
			$tabs = $fieldset['tabs'] ?? [
				['fields' => $fieldset['fields'] ?? []]
			];

			foreach ($tabs as $tab) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFields($tab['fields'] ?? [])
				];
			}
		}

		return $templates;
	}

	/**
	 * Checks if a field type supports file uploads
	 * @since 6.0.0
	 */
	protected function supportsUploads(string|null $type): bool
	{
		$class = Field::$types[$type] ?? null;

		if (is_string($class) === false || class_exists($class) === false) {
			return false;
		}

		$traits = [];

		for ($parent = $class; $parent !== false; $parent = get_parent_class($parent)) {
			$traits = [...$traits, ...(class_uses($parent) ?: [])];
		}

		return isset($traits[Upload::class]);
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
