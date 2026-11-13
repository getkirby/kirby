<?php

namespace Kirby\Form\Mixin;

use Closure;
use Kirby\Api\Api;
use Kirby\Blueprint\FileBlueprint;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Reflection\Attributes\Prop;

/**
 * Provides upload options and file linking functionality
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Upload
{
	/**
	 * Sets the upload options for linked files: a blueprint name or
	 * `blueprint` and `parent` (`template` is an alias for `blueprint`)
	 */
	#[Prop(computed: true)]
	protected mixed $uploads = null;

	/**
	 * Accepts files with the upload blueprint,
	 * if the uploads go to the field's own model
	 * (only used if the field implements `ProvidesAcceptedFileBlueprints`)
	 *
	 * @return list<string>
	 * @since 6.0.0
	 */
	public function acceptedFileBlueprints(): array
	{
		try {
			$uploads = $this->uploads();

			if ($uploads === false) {
				return [];
			}

			$parent = $this->uploadParent($uploads['parent'] ?? null);
		} catch (InvalidArgumentException) {
			// nothing can be uploaded to an unresolvable parent
			return [];
		}

		$model = $this->model();

		if ($parent::class !== $model::class || $parent->is($model) === false) {
			return [];
		}

		return [$uploads['blueprint'] ?? 'default'];
	}

	/**
	 * Uploads and creates files via the API upload handler
	 * @codeCoverageIgnore
	 */
	public function upload(
		Api $api,
		array|false $params,
		Closure $map
	): array {
		if ($params === false) {
			throw new Exception(
				message: 'Uploads are disabled for this field'
			);
		}

		$parent = $this->uploadParent($params['parent'] ?? null);

		return $api->upload(
			callback: function ($source, $filename, $template) use ($parent, $map) {
				$props = [
					'source'   => $source,
					'template' => $template,
					'filename' => $filename,
				];

				// move the source file from the temp dir
				$file = $parent->createFile($props, true);

				if ($file instanceof File === false) {
					throw new Exception(
						message: 'The file could not be uploaded'
					);
				}

				return $map($file, $parent);
			},
			template: $params['blueprint'] ?? null
		);
	}

	protected function uploadParent(
		string|null $parentQuery = null
	): Site|Page|User {
		$parent = $this->model();

		if ($parentQuery) {
			$parent = $parent->query($parentQuery);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		// query results that cannot hold files are no valid parent
		if (
			$parent instanceof Site ||
			$parent instanceof Page ||
			$parent instanceof User
		) {
			return $parent;
		}

		throw new InvalidArgumentException(
			message: '"' . $parentQuery . '" could not be resolved as a valid parent for the upload'
		);
	}

	/**
	 * Returns the normalized upload options
	 * or `false` if uploads are disabled
	 *
	 * @return array<string, mixed>|false
	 */
	public function uploads(): array|false
	{
		$uploads = $this->uploads;

		if ($uploads === false) {
			return false;
		}

		$uploads = match (true) {
			is_string($uploads) => ['blueprint' => $uploads],
			is_array($uploads)  => $uploads,
			default             => []
		};

		// `template` is an alias for `blueprint`
		$uploads['blueprint'] ??= $uploads['template'] ?? null;
		unset($uploads['template']);

		$uploads = [
			'accept'    => '*',
			'blueprint' => null,
			'parent'    => null,
			...$uploads
		];

		if (method_exists($this, 'image') === true) {
			$uploads['preview'] = $this->image();
		}

		$blueprint = $uploads['blueprint'];

		if ($blueprint !== null && $blueprint !== '') {
			$uploads['accept'] = FileBlueprint::factory(
				$this->uploadParent($uploads['parent']),
				'files/' . $blueprint,
				'files/default'
			)?->acceptAttribute();
		}

		return array_filter($uploads);
	}
}
