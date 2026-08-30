<?php

namespace Kirby\Exception;

use Error;
use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

class WillFail
{
	public function fail(): never
	{
		throw new Exception(key: 'key.unique');
	}
}

#[CoversClass(Exception::class)]
class ExceptionTest extends TestCase
{
	protected function tearDown(): void
	{
		App::destroy();

		I18n::$locale       = 'en';
		I18n::$translations = [];
	}

	public function testDebugInfo(): void
	{
		$exception = new Exception(
			key: 'test.lazy',
			fallback: 'The page slug "{ slug }" is invalid',
			data: ['slug' => 'project/(c']
		);

		// the lazily resolved message must not show up as an
		// uninitialized property in debug output
		$this->assertContains(
			'The page slug "project/(c" is invalid',
			$exception->__debugInfo()
		);
	}

	public function testDefaults(): void
	{
		$exception = new Exception();

		$this->assertSame('error.general', $exception->getKey());
		$this->assertSame('An error occurred', $exception->getMessage());
		$this->assertSame(500, $exception->getHttpCode());
		$this->assertSame([], $exception->getData());
		$this->assertSame([], $exception->getDetails());
	}

	public function testException(): void
	{
		$exception = new Exception(
			key: 'page.slug.invalid',
			fallback: 'The page slug "{ slug }" is invalid',
			data: $data = ['slug' => 'project/(c'],
			details: $details = ['some' => 'details'],
			httpCode: $http = 500,
			translate: false
		);

		$this->assertInstanceOf(Exception::class, $exception);
		$this->assertSame('error.page.slug.invalid', $exception->getKey());
		$this->assertSame('error.page.slug.invalid', $exception->getCode());
		$this->assertSame('The page slug "project/(c" is invalid', $exception->getMessage());
		$this->assertSame($http, $exception->getHttpCode());
		$this->assertSame($data, $exception->getData());
		$this->assertSame($details, $exception->getDetails());
	}

	public function testGetDetails(): void
	{
		$exception = new Exception(
			details: $details = [
				[
					'label'   => 'A',
					'message'   => 'Message A',
				]
			]
		);

		$this->assertSame($details, $exception->getDetails());
	}

	public function testGetDetailsWithExceptions(): void
	{
		$exception = new Exception(
			details: [
				'A' => new Exception(message: 'Message A')
			]
		);

		$expected = [
			'A' => [
				'label'   => 'A',
				'message'   => 'Message A',
			]
		];

		$this->assertSame($expected, $exception->getDetails());
	}

	public function testGetFileRelative(): void
	{
		$exception = new Exception();
		$this->assertSame(__FILE__, $exception->getFileRelative());

		// an empty document root is the same as none at all
		new App([
			'server' => [
				'DOCUMENT_ROOT' => ''
			]
		]);

		$this->assertSame(__FILE__, $exception->getFileRelative());

		new App([
			'server' => [
				'DOCUMENT_ROOT' => __DIR__
			]
		]);

		$this->assertSame(F::filename(__FILE__), $exception->getFileRelative());

		new App([
			'server' => [
				'DOCUMENT_ROOT' => __DIR__ . '/'
			]
		]);

		$this->assertSame(F::filename(__FILE__), $exception->getFileRelative());
	}

	public function testGetInaccessibleProperty(): void
	{
		$exception = new Exception(key: 'test.lazy');

		// `::__get()` only serves the lazy message; it must not
		// turn the protected properties into public ones
		$this->expectException(Error::class);
		$this->expectExceptionMessage(
			'Cannot access property Kirby\\Exception\\Exception::$data'
		);

		$exception->data;
	}

	public function testJustMessage(): void
	{
		$exception = new Exception('Another error occurred');

		$this->assertSame('error.general', $exception->getKey());
		$this->assertSame('Another error occurred', $exception->getMessage());
		$this->assertSame(500, $exception->getHttpCode());
		$this->assertSame([], $exception->getData());
	}

	public function testJustMessageWithNamedArgument(): void
	{
		$exception = new Exception(message: 'Another error occurred');

		$this->assertSame('error.general', $exception->getKey());
		$this->assertSame('Another error occurred', $exception->getMessage());
		$this->assertSame(500, $exception->getHttpCode());
		$this->assertSame([], $exception->getData());
	}

	public function testKeyWithPrefix(): void
	{
		// a key that already carries the prefix is not prefixed twice
		$exception = new Exception(key: 'error.test.lazy');

		$this->assertSame('error.test.lazy', $exception->getKey());
	}

	public function testMessageIsResolvedLazily(): void
	{
		$exception = new Exception(key: 'translatable');

		// the translations are only registered after the exception
		// was created, so an eagerly resolved message would already
		// have fallen back to the default
		I18n::$locale       = 'test';
		I18n::$translations = [
			'test' => ['error.translatable' => 'Some translatable error']
		];

		$this->assertSame('Some translatable error', $exception->getMessage());
	}

	public function testMessageIsResolvedOnce(): void
	{
		I18n::$locale       = 'test';
		I18n::$translations = [
			'test' => ['error.translatable' => 'Some translatable error']
		];

		$exception = new Exception(key: 'translatable');
		$this->assertSame('Some translatable error', $exception->getMessage());

		// the resolved message is cached in the property, so a
		// later change to the translations is not picked up again
		I18n::$translations = [
			'test' => ['error.translatable' => 'Some other error']
		];

		$this->assertSame('Some translatable error', $exception->getMessage());
	}

	public function testPHPUnitTesting(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.key.unique');

		$class = new WillFail();
		$class->fail();
	}

	public function testPrevious(): void
	{
		$previous  = new Exception(message: 'Previous');
		$exception = new Exception(previous: $previous);

		$this->assertNull($previous->getPrevious());
		$this->assertSame($previous, $exception->getPrevious());
	}

	public function testPreviousWithMessage(): void
	{
		$previous  = new Exception(message: 'Previous');
		$exception = new Exception(
			message: 'Something went wrong',
			previous: $previous
		);

		$this->assertSame($previous, $exception->getPrevious());
	}

	public function testSerialize(): void
	{
		$exception = new Exception(
			key: 'test.lazy',
			fallback: 'The page slug "{ slug }" is invalid',
			data: ['slug' => 'project/(c'],
			httpCode: 400
		);

		// the lazy message has to be resolved before the object is
		// serialized, as an uninitialized property would not
		// survive the round trip
		$this->assertContains(
			'The page slug "project/(c" is invalid',
			$exception->__serialize()
		);
	}

	public function testSerializeRoundTrip(): void
	{
		// a trace that captured its arguments holds the closures of
		// the test runner, which no exception can serialize
		$args = ini_set('zend.exception_ignore_args', '1');

		$exception = new Exception(
			key: 'test.lazy',
			fallback: 'The page slug "{ slug }" is invalid',
			data: ['slug' => 'project/(c']
		);

		$restored = unserialize(serialize($exception));

		ini_set('zend.exception_ignore_args', $args);

		$this->assertSame(
			'The page slug "project/(c" is invalid',
			$restored->getMessage()
		);
	}

	public function testToArray(): void
	{
		$exception = new Exception();
		$expected = [
			'exception' => Exception::class,
			'message'   => 'An error occurred',
			'key'       => 'error.general',
			'file'      => __FILE__,
			'line'      => $exception->getLine(),
			'details'   => [],
			'code'      => 500
		];
		$this->assertSame($expected, $exception->toArray());

		new App([
			'server' => [
				'DOCUMENT_ROOT' => __DIR__ . '/'
			]
		]);

		$exception = new Exception();
		$expected['file'] = F::filename(__FILE__);
		$expected['line'] = $exception->getLine();
		$this->assertSame($expected, $exception->toArray());
	}

	public function testTranslation(): void
	{
		I18n::$locale = 'test';
		I18n::$translations = [
			'test' => [
				'error.general'      => 'Some general error',
				'error.translatable' => 'Some other translatable error'
			]
		];

		// scenario 1: translation for provided key in current language
		$exception = new Exception(
			key: 'translatable',
			fallback: 'Some fallback'
		);
		$this->assertSame('Some other translatable error', $exception->getMessage());

		// scenario 3: provided fallback message
		$exception = new Exception(
			key: 'not-translated',
			fallback: 'Some fallback'
		);
		$this->assertSame('Some fallback', $exception->getMessage());

		// scenario 4: translation for default key in current language
		$exception = new Exception(
			key: 'not-translated',
		);
		$this->assertSame('Some general error', $exception->getMessage());

		I18n::$translations = [
			'test' => [
				'error.general'      => 'Some general fallback',
				'error.translatable' => 'Some other translatable fallback'
			]
		];

		// scenario 2: translation for provided key in default language
		$exception = new Exception(
			key: 'translatable',
			fallback: 'Some fallback'
		);
		$this->assertSame('Some other translatable fallback', $exception->getMessage());

		// scenario 5: translation for default key in default language
		$exception = new Exception(
			key: 'not-translated'
		);
		$this->assertSame('Some general fallback', $exception->getMessage());

		I18n::$locale = 'en';
		I18n::$translations = [];

		// scenario 6: default fallback message
		$exception = new Exception(
			key: 'translatable'
		);
		$this->assertSame('An error occurred', $exception->getMessage());
	}

}
