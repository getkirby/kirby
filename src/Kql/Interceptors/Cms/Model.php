<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Model extends Interceptor
{
	public const CLASS_ALIAS = 'model';

	public function __call($method, array $args = [])
	{
		if ($this->isAllowedMethod($method) === true) {
			return $this->object->$method(...$args);
		}

		if (method_exists($this->object, $method) === false) {
			return $this->object->content()->get($method);
		}

		$this->forbiddenMethod($method);
	}

	protected function allowedMethodsForChildren()
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

	protected function allowedMethodsForFiles()
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

	protected function allowedMethodsForModels()
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

	protected function allowedMethodsForSiblings()
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

	protected function allowedMethodsForParents()
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
