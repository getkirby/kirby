<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

class SitePagesTest extends TestCase
{
	public function testErrorPage()
	{
		$site = new Site([
			'children' => [
				['slug' => 'error']
			]
		]);

		$this->assertIsPage('error', $site->errorPage());
	}

	public function testHomePage()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertIsPage('home', $site->homePage());
	}

	public function testPage()
	{
		$site = new Site([
			'page' => $page = new Page(['slug' => 'test'])
		]);

		$this->assertIsPage($page, $site->page());
	}

	public function testDefaultPageWithChildren()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertIsPage('home', $site->page());
	}

	public function testPageWithPathAndChildren()
	{
		$site = new Site([
			'children' => [
				['slug' => 'test']
			]
		]);

		$this->assertIsPage('test', $site->page('test'));
	}

	public function testVisitWithPageObject()
	{
		$site = new Site();
		$page = $site->visit(new Page(['slug' => 'test']));

		$this->assertIsPage('test', $site->page());
		$this->assertIsPage($page, $site->page());
	}

	public function testVisitWithId()
	{
		$site = new Site([
			'children' => [
				['slug' => 'test']
			]
		]);

		$page = $site->visit('test');

		$this->assertIsPage('test', $site->page());
		$this->assertIsPage($page, $site->page());
	}

	public function testVisitInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid page object');

		$site = new Site();
		$site->visit('nonexists');
	}

	public function testSearch()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'foo-a'],
				['slug' => 'bar-b'],
			]
		]);

		$collection = $site->search('foo');
		$this->assertCount(2, $collection);
	}

	public function testSearchMinlength()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'foo-a'],
				['slug' => 'bar-b'],
			]
		]);

		$collection = $site->search('foo', [
			'minlength' => 5
		]);

		$this->assertCount(0, $collection);
	}

	public function testSearchStopWords()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'baz'],
				['slug' => 'foo-bar'],
				['slug' => 'foo-baz'],
			]
		]);

		$collection = $site->search('foo bar', [
			'stopwords' => ['bar']
		]);

		$this->assertCount(3, $collection);
	}

	public function testSearchStopWordsNoResults()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar'],
				['slug' => 'baz'],
				['slug' => 'foo-bar'],
				['slug' => 'foo-baz'],
			]
		]);

		$collection = $site->search('new foo', [
			'stopwords' => ['foo']
		]);

		$this->assertCount(0, $collection);
	}

	public function testPages()
	{
		$site = new Site([
			'children' => [
				['slug' => 'home'],
				['slug' => 'foo'],
				['slug' => 'bar']
			]
		]);

		$collection = $site->pages();
		$this->assertCount(3, $collection);
	}
}
