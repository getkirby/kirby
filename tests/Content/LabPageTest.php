<?php

namespace Kirby\Content;

use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;

class LabPageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.LabPage';

	public function testCloneMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article',
		]);

		Data::write($page->root() . '/article.en.txt', $contentEN = [
			'title' => 'Title English'
		]);

		Data::write($page->root() . '/article.de.txt', $contentDE = [
			'title' => 'Title Deutsch'
		]);

		$this->assertInstanceOf(PlainTextContentStorageHandler::class, $page->storage());

		$clone = $page->clone();

		$this->assertInstanceOf(MemoryContentStorageHandler::class, $page->storage());
		$this->assertInstanceOf(PlainTextContentStorageHandler::class, $clone->storage());

		// check the content of the original
		$this->assertSame($contentEN, $page->version()->read('en'));
		$this->assertSame($contentDE, $page->version()->read('de'));

		// check the content of the clone
		$this->assertSame($contentEN, $clone->version()->read('en'));
		$this->assertSame($contentDE, $clone->version()->read('de'));

		// modify the content of the model on disk
		Data::write($clone->root() . '/article.en.txt', $updatedContentEN = [
			'title' => 'Updated Title English'
		]);

		Data::write($clone->root() . '/article.de.txt', $updatedContentDE = [
			'title' => 'Updated Title Deutsch'
		]);

		// check the content of the clone
		$this->assertSame($updatedContentEN, $clone->version()->read('en'));
		$this->assertSame($updatedContentDE, $clone->version()->read('de'));

		// check that the content of the original is still the same as before
		$this->assertSame($contentEN, $page->version()->read('en'));
		$this->assertSame($contentDE, $page->version()->read('de'));
	}

	public function testCloneSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article',
		]);

		Data::write($page->root() . '/article.txt', $content = [
			'title' => 'Title'
		]);

		$this->assertInstanceOf(PlainTextContentStorageHandler::class, $page->storage());

		$clone = $page->clone();

		$this->assertInstanceOf(MemoryContentStorageHandler::class, $page->storage());
		$this->assertInstanceOf(PlainTextContentStorageHandler::class, $clone->storage());

		// check the content of the original
		$this->assertSame($content, $page->version()->read());

		// check the content of the clone
		$this->assertSame($content, $clone->version()->read());

		// modify the content of the model on disk
		Data::write($clone->root() . '/article.txt', $updatedContent = [
			'title' => 'Updated Title'
		]);

		// check the content of the clone
		$this->assertSame($updatedContent, $clone->version()->read());

		// check that the content of the original is still the same as before
		$this->assertSame($content, $page->version()->read());
	}

	public function testContentMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($page->root() . '/article.en.txt', $content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $page->content('en')->toArray());

		$this->markTestIncomplete('TODO: The following assertion is only correct as long as we don’t merge translated content. It’s a breaking change so far compared to the previous behavior and needs to be fixed.');
		$this->assertSame([], $page->content('de')->toArray());
	}

	public function testContentSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($page->root() . '/article.txt', $content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $page->content()->toArray());
	}

	public function testContentNonExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug' => 'test'
		]);

		$this->assertSame([], $page->content('en')->toArray());
		$this->assertSame([], $page->content('de')->toArray());
	}

	public function testContentNonExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug' => 'test'
		]);

		$this->assertSame([], $page->content()->toArray());
	}

	public function testSetContentMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'    => 'test',
			'content' => $content = [
				'title' => 'Test'
			]
		]);

		$this->assertSame($content, $page->content()->toArray());
		$this->assertSame($content, $page->content('en')->toArray());

		$this->markTestIncomplete('TODO: The following assertion is only correct as long as we don’t merge translated content. It’s a breaking change so far compared to the previous behavior and needs to be fixed.');
		$this->assertSame([], $page->content('de')->toArray());
	}

	public function testSetContentSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'    => 'test',
			'content' => $content = [
				'title' => 'Test'
			]
		]);

		$this->assertSame($content, $page->content()->toArray());
	}

	public function testSetTranslationMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'         => 'test',
			'translations' => [
				[
					'code'    => 'en',
					'content' => $en = [
						'title' => 'Test English'
					]
				],
				[
					'code'    => 'de',
					'content' => $de = [
						'title' => 'Test Deutsch'
					]
				]
			]
		]);

		$this->assertSame($en, $page->content()->toArray());
		$this->assertSame($en, $page->content('en')->toArray());
		$this->assertSame($en, $page->translation('en')->content());

		$this->assertSame($de, $page->content('de')->toArray());
		$this->assertSame($de, $page->translation('de')->content());
	}

	public function testSetTranslationSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'         => 'test',
			'translations' => [
				[
					'code'    => 'en',
					'content' => $content = [
						'title' => 'Test'
					]
				]
			]
		]);

		$this->assertSame($content, $page->content()->toArray());
		$this->assertSame($content, $page->translation('en')->content());
	}

	public function testSlugMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					// should be ignored
					'slug' => 'english-slug'
				],
				[
					'code' => 'de',
					// should be considered
					'slug' => 'deutscher-slug'
				]
			]
		]);

		// the slug for the default language will always be the slug prop
		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->slug('en'));

		// the slug for non-default languages can be customized
		$this->assertSame('deutscher-slug', $page->slug('de'));
	}

	public function testSlugSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => $content = [
						'title' => 'Test'
					],
					// setting a custom slug should not have any effect
					// in single language setups. The slug prop is still
					// the dominant factor here.
					'slug' => 'foo'
				]
			]
		]);

		$this->assertSame('test', $page->slug());
	}

	public function testSlugWithInvalidLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug' => 'test'
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$page->slug('fr');
	}

	public function testTranslationMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		Data::write($page->root() . '/article.en.txt', $en = [
			'title' => 'Test English'
		]);

		Data::write($page->root() . '/article.de.txt', $de = [
			'title' => 'Test Deutsch'
		]);

		$this->assertSame($en, $page->translation('en')->content());
		$this->assertSame($de, $page->translation('de')->content());
	}

	public function testTranslationSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		Data::write($page->root() . '/article.txt', $content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $page->translation()->content());
		$this->assertSame($content, $page->translation('en')->content());
	}

	public function testTranslationsMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		Data::write($page->root() . '/article.en.txt', $en = [
			'title' => 'Test English'
		]);

		Data::write($page->root() . '/article.de.txt', $de = [
			'title' => 'Test Deutsch'
		]);

		$translations = $page->translations();

		$this->assertCount(2, $translations);
		$this->assertSame($en, $translations->find('en')->content());
		$this->assertSame($de, $translations->find('de')->content());
	}

	public function testTranslationsSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($page->root() . '/article.txt', $content = [
			'title' => 'Test'
		]);

		$translations = $page->translations();

		$this->assertCount(1, $translations);
		$this->assertSame($content, $translations->first()->content());
	}

	public function testUpdateMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		// make sure to be authenticated
		$this->app->impersonate('kirby');

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($page->root() . '/article.en.txt', $en = [
			'title' => 'Test English'
		]);

		Data::write($page->root() . '/article.de.txt', $de = [
			'title' => 'Test Deutsch'
		]);

		$updatedPage = $page->update([
			'title' => 'Updated Test English'
		], 'en');

		$updatedPage = $page->update([
			'title' => 'Updated Test Deutsch'
		], 'de');

		// check if the old version is still the same
		$this->assertSame('Test English', $page->title()->value());

		$this->assertSame('Updated Test English', $updatedPage->title()->value());
		$this->assertSame('Updated Test English', $updatedPage->content('en')->title()->value());
		$this->assertSame('Updated Test English', Data::read($updatedPage->root() . '/article.en.txt')['title']);

		$this->assertSame('Updated Test Deutsch', $updatedPage->content('de')->title()->value());
		$this->assertSame('Updated Test Deutsch', Data::read($updatedPage->root() . '/article.de.txt')['title']);
	}

	public function testUpdateSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$page = new LabPage([
			'slug'     => 'test',
			'template' => 'article'
		]);

		// make sure to be authenticated
		$this->app->impersonate('kirby');

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($page->root() . '/article.txt', $content = [
			'title' => 'Test'
		]);

		$updatedPage = $page->update([
			'title' => 'Updated title'
		]);

		// check if the old version is still the same
		$this->assertSame('Test', $page->title()->value());

		$this->assertSame('Updated title', $updatedPage->title()->value());
		$this->assertSame('Updated title', Data::read($updatedPage->root() . '/article.txt')['title']);
	}
}
