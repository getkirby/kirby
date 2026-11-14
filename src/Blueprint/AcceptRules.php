<?php

namespace Kirby\Blueprint;

use Kirby\Form\Fields;

/**
 * The AcceptRules class goes through all fields of a blueprint
 * and collects which blueprints new files and pages may use
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class AcceptRules
{
	/**
	 * @var list<string>|null
	 */
	protected array|null $fileBlueprints = null;

	/**
	 * @var list<string>|null
	 */
	protected array|null $pageBlueprints = null;

	public function __construct(
		protected Blueprint $blueprint,
	) {
	}

	/**
	 * Builds the field objects of all fields or of the given field,
	 * which also collect the blueprints of their nested fields
	 */
	protected function fields(string|null $inField = null): Fields
	{
		$fields = match ($inField) {
			null    => $this->blueprint->fields(),
			default => array_filter([$inField => $this->blueprint->field($inField)])
		};

		return new Fields(
			fields: $fields,
			model: $this->blueprint->model()
		);
	}

	/**
	 * Gathers what file blueprints are allowed in
	 * this model based on the blueprint
	 *
	 * @return list<string>
	 */
	public function fileBlueprints(string|null $inField = null): array
	{
		// get cached results for the current file model
		// (except when collecting for a specific field)
		if ($inField === null && $this->fileBlueprints !== null) {
			return $this->fileBlueprints; // @codeCoverageIgnore
		}

		$blueprints = $this->fields($inField)->acceptedFileBlueprints();

		// no caching for when collecting for a specific field
		if ($inField !== null) {
			return $blueprints; // @codeCoverageIgnore
		}

		return $this->fileBlueprints = $blueprints;
	}

	/**
	 * Gathers what blueprints pages created
	 * through the fields of this model may use
	 *
	 * @return list<string>
	 */
	public function pageBlueprints(string|null $inField = null): array
	{
		// get cached results for the current model
		// (except when collecting for a specific field)
		if ($inField === null && $this->pageBlueprints !== null) {
			return $this->pageBlueprints;
		}

		$blueprints = $this->fields($inField)->acceptedPageBlueprints();

		// no caching for when collecting for a specific field
		if ($inField !== null) {
			return $blueprints;
		}

		return $this->pageBlueprints = $blueprints;
	}
}
