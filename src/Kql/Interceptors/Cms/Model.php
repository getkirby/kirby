<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptor;

class Model extends Interceptor
{
	public const CLASS_ALIAS = 'model';

	public function __call($method, array $args = []): mixed
	{
		if ($this->isAllowedMethod($method) === true) {
			return $this->object->$method(...$args);
		}

		if (method_exists($this->object, $method) === false) {
			return $this->object->content()->get($method);
		}

		$this->forbiddenMethod($method);
	}

	protected function allowedMethodsForChildren(): array
	{
		return [
			'children',
			'childrenAndDrafts',
			'draft',
			'drafts',
			'find',
			'findPageOrDraft',
			'grandChildren',
			'hasChildren',
			'hasDrafts',
			'hasListedChildren',
			'hasUnlistedChildren',
			'index',
			'search',
		];
	}

	protected function allowedMethodsForFiles(): array
	{
		return [
			'audio',
			'code',
			'documents',
			'file',
			'files',
			'hasAudio',
			'hasCode',
			'hasDocuments',
			'hasFiles',
			'hasImages',
			'hasVideos',
			'image',
			'images',
			'videos'
		];
	}

	protected function allowedMethodsForModels(): array
	{
		return [
			'apiUrl',
			'blueprint',
			'content',
			'dragText',
			'exists',
			'id',
			'mediaUrl',
			'modified',
			'permissions',
			'panel',
			'permalink',
			'previewUrl',
			'url',
		];
	}

	protected function allowedMethodsForSiblings(): array
	{
		return [
			'indexOf',
			'next',
			'nextAll',
			'prev',
			'prevAll',
			'siblings',
			'hasNext',
			'hasPrev',
			'isFirst',
			'isLast',
			'isNth'
		];
	}

	protected function allowedMethodsForParents(): array
	{
		return [
			'parent',
			'parentId',
			'parentModel',
			'site',
		];
	}

	public function uuid(): string
	{
		return $this->object->uuid()->toString();
	}
}
