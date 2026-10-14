<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Form\Field\BlocksField;
use Kirby\Form\Field\FileListField;
use Kirby\Form\Field\LayoutField;
use Kirby\Form\Fields;
use Kirby\Form\Interface\ProvidesNestedForm;

/**
 * The AcceptRules class goes through all fields of a
 * blueprint and collects rules for accepted files
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
			default => array_filter([$inField => $this->blueprint->field($inField)])
		};

		// …through the field objects, which resolve
		// their props (e.g. the fieldsets of blocks)
		$fields = new Fields(
			fields: $fields,
			model: $this->blueprint->model()
		);

		$templates = $this->fileTemplatesFromFields($fields);

		// no caching for when collecting for a specific field
		if ($inField !== null) {
			return $templates; // @codeCoverageIgnore
		}

		return $this->fileTemplates = $templates;
	}

	/**
	 * Gathers the allowed file templates from fields
	 */
	public function fileTemplatesFromFields(Fields $fields): array
	{
		$templates = [];

		foreach ($fields as $field) {
			// file lists accept uploads for their own template
			if ($field instanceof FileListField) {
				$template  = $field->template();
				$templates = [
					...$templates,
					...($template
						? [$template]
						: App::instance()->blueprints('files'))
				];
				continue;
			}

			// blocks and layout fields
			if ($field instanceof BlocksField) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFieldsets($field)
				];
				continue;
			}

			// structure and object fields
			if ($field instanceof ProvidesNestedForm) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFields($field->form()->fields())
				];
				continue;
			}

			// fields that support uploads
			if (method_exists($field, 'uploads') === true) {
				$templates = [
					...$templates,
					...$this->fileTemplatesFromFieldUploads($field->uploads())
				];
			}
		}

		return $templates;
	}

	/**
	 * Gathers the allowed file templates from the fieldsets
	 * of a blocks or layout field and the layout settings
	 */
	public function fileTemplatesFromFieldsets(BlocksField $field): array
	{
		$templates = [];

		foreach ($field->fieldsets() as $fieldset) {
			$form      = $field->form($fieldset->fields());
			$templates = [
				...$templates,
				...$this->fileTemplatesFromFields($form->fields())
			];
		}

		if ($field instanceof LayoutField) {
			$templates = [
				...$templates,
				...$this->fileTemplatesFromFields($field->attrsForm()->fields())
			];
		}

		return $templates;
	}

	/**
	 * Extracts templates from field uploads settings
	 */
	public function fileTemplatesFromFieldUploads(array|false $uploads): array
	{
		if ($uploads === false) {
			return [];
		}

		// only if the `uploads` parent is this model
		if ($target = $uploads['parent'] ?? null) {
			if ($this->blueprint->model()->id() !== $target) {
				return [];
			}
		}

		return [($uploads['template'] ?? 'default')];
	}
}
