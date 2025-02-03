<?php

namespace Kirby\Panel;

use Kirby\TestCase;
use Kirby\Toolkit\I18n;

/**
 * @coversDefaultClass \Kirby\Panel\Area
 */
class AreaTest extends TestCase
{
	public function tearDown(): void
	{
		I18n::$translations = [];
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$area = new Area(id: 'test');

		$this->assertSame([], $area->breadcrumb());
		$this->assertSame('test', $area->breadcrumbLabel());
		$this->assertSame([], $area->buttons());
		$this->assertNull($area->current());
		$this->assertNull($area->dialog());
		$this->assertSame([], $area->dialogs());
		$this->assertNull($area->drawer());
		$this->assertSame([], $area->drawers());
		$this->assertSame([], $area->dropdowns());
		$this->assertNull($area->icon());
		$this->assertSame('test', $area->label());
		$this->assertSame('test', $area->link());
		$this->assertNull($area->search());
		$this->assertSame([], $area->searches());
		$this->assertSame([], $area->requests());
		$this->assertSame('test', $area->title());
		$this->assertSame([], $area->views());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabel()
	{
		$area = new Area(
			id: 'test',
			breadcrumbLabel: 'Label'
		);

		$this->assertSame('Label', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabelWithClosureDefinition()
	{
		$area = new Area(
			id: 'test',
			breadcrumbLabel: function () {
				return 'Breadcrumb Label';
			}
		);

		$this->assertSame('Breadcrumb Label', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabelWithIdAsDefault()
	{
		$area = new Area(
			id: 'test',
		);

		$this->assertSame('test', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabelWithLabelAsDefault()
	{
		$area = new Area(
			id: 'test',
			label: 'Test Label',
		);

		$this->assertSame('Test Label', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabelWithTranslationString()
	{
		I18n::$translations = [
			'en' => [
				'test' => 'Test Label'
			]
		];

		$area = new Area(
			id: 'test',
			breadcrumbLabel: 'test'
		);

		$this->assertSame('Test Label', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::breadcrumbLabel
	 */
	public function testBreadcrumbLabelWithTranslationArray()
	{
		$area = new Area(
			id: 'test',
			breadcrumbLabel: [
				'en' => 'Test Label',
				'de' => 'Töst Label'
			]
		);

		$this->assertSame('Test Label', $area->breadcrumbLabel());
	}

	/**
	 * @covers ::isAccessible
	 */
	public function testIsAccessible()
	{
		$area = new Area(id: 'test');

		$this->assertTrue($area->isAccessible([]));
		$this->assertFalse($area->isAccessible([
			'access' => [
				'test' => false
			]
		]));
	}

	/**
	 * @covers ::isCurrent
	 */
	public function testIsCurrent()
	{
		$area = new Area(id: 'test');

		$this->assertFalse($area->isCurrent());
		$this->assertTrue($area->isCurrent('test'));
	}

	/**
	 * @covers ::isCurrent
	 */
	public function testIsCurrentWithCustomCurrentSetting()
	{
		$area = new Area(
			id: 'test',
			current: true
		);

		$this->assertTrue($area->isCurrent());
		$this->assertTrue($area->isCurrent('foo'));
		$this->assertTrue($area->isCurrent('bar'));

		$area = new Area(
			id: 'test',
			current: function ($current) {
				return $current === 'foo';
			}
		);

		$this->assertFalse($area->isCurrent());
		$this->assertFalse($area->isCurrent('bar'));
		$this->assertTrue($area->isCurrent('foo'));
	}

	/**
	 * @covers ::label
	 */
	public function testLabel()
	{
		$area = new Area(
			id: 'test',
			label: 'Label'
		);

		$this->assertSame('Label', $area->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWithClosureDefinition()
	{
		$area = new Area(
			id: 'test',
			label: function () {
				return 'Label';
			}
		);

		$this->assertSame('Label', $area->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWithIdAsDefault()
	{
		$area = new Area(
			id: 'test',
		);

		$this->assertSame('test', $area->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWithTranslationString()
	{
		I18n::$translations = [
			'en' => [
				'test' => 'Test Label'
			]
		];

		$area = new Area(
			id: 'test',
			label: 'test'
		);

		$this->assertSame('Test Label', $area->label());
	}

	/**
	 * @covers ::label
	 */
	public function testLabelWithTranslationArray()
	{
		$area = new Area(
			id: 'test',
			label: [
				'en' => 'Test Label',
				'de' => 'Töst Label'
			]
		);

		$this->assertSame('Test Label', $area->label());
	}

	/**
	 * @covers ::link
	 */
	public function testLink()
	{
		$area = new Area(
			id: 'test',
			link: 'custom-link'
		);

		$this->assertSame('custom-link', $area->link());
	}

	/**
	 * @covers ::link
	 */
	public function testLinkWithIdAsDefault()
	{
		$area = new Area(id: 'test');

		$this->assertSame('test', $area->link());
	}

	/**
	 * @covers ::merge
	 */
	public function testMerge()
	{
		$area = new Area(id: 'test');

		$this->assertSame('test', $area->link());

		$area->merge([
			'link' => 'custom-link'
		]);

		$this->assertSame('custom-link', $area->link());
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettings()
	{
		$area = new Area(id: 'test');

		$this->assertFalse($area->menuSettings());
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettingsWithEnabledMenu()
	{
		$area = new Area(
			id: 'test',
			menu: true,
		);

		$this->assertSame([], $area->menuSettings());
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettingsWithDisabledAccess()
	{
		$area = new Area(
			id: 'test',
			menu: true,
		);

		$this->assertFalse($area->menuSettings(
			permissions: [
				'access' => [
					'test' => false
				]
			]
		));
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettingsWithClosureDefinition()
	{
		$menu = [
			'icon' => 'edit'
		];

		$passedAreas = [
			new Area('sibling')
		];

		$passedPermissions = [
			'access' => [
				'test' => true
			]
		];

		$area = new Area(
			id: 'test',
			menu: function ($areas, $permissions, $current) use ($menu, $passedAreas, $passedPermissions) {
				$this->assertSame($areas, $passedAreas);
				$this->assertSame($permissions, $passedPermissions);
				$this->assertSame($current, 'test');

				return $menu;
			}
		);

		$this->assertSame($menu, $area->menuSettings(
			areas: $passedAreas,
			permissions: $passedPermissions,
			current: 'test'
		));
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettingsWithArrayDefinition()
	{
		$area = new Area(
			id: 'test',
			menu: $menu = [
				'icon' => 'edit'
			]
		);

		$this->assertSame($menu, $area->menuSettings());
	}

	/**
	 * @covers ::menuSettings
	 */
	public function testMenuSettingsWithDisabledFlag()
	{
		$area = new Area(
			id: 'test',
			menu: 'disabled'
		);

		$this->assertSame(['disabled' => true], $area->menuSettings());
	}

	/**
	 * @covers ::title
	 */
	public function testTitle()
	{
		$area = new Area(
			id: 'test',
			title: 'Title'
		);

		$this->assertSame('Title', $area->title());
	}

	/**
	 * @covers ::title
	 */
	public function testTitleWithClosureDefinition()
	{
		$area = new Area(
			id: 'test',
			title: function () {
				return 'Title';
			}
		);

		$this->assertSame('Title', $area->title());
	}

	/**
	 * @covers ::title
	 */
	public function testTitleWithIdAsDefault()
	{
		$area = new Area(
			id: 'test',
		);

		$this->assertSame('test', $area->title());
	}

	/**
	 * @covers ::title
	 */
	public function testTitleWithLabelAsDefault()
	{
		$area = new Area(
			id: 'test',
			label: 'Test Label'
		);

		$this->assertSame('Test Label', $area->title());
	}

	/**
	 * @covers ::title
	 */
	public function testTitleWithTranslationString()
	{
		I18n::$translations = [
			'en' => [
				'test' => 'Test Title'
			]
		];

		$area = new Area(
			id: 'test',
			title: 'test'
		);

		$this->assertSame('Test Title', $area->title());
	}

	/**
	 * @covers ::title
	 */
	public function testTitleWithTranslationArray()
	{
		$area = new Area(
			id: 'test',
			title: [
				'en' => 'Test Title',
				'de' => 'Töst Title'
			]
		);

		$this->assertSame('Test Title', $area->title());
	}

	/**
	 * @covers ::toView
	 */
	public function testToView()
	{
		$area = new Area(
			id: 'test'
		);

		$expected = [
			'breadcrumb'      => [],
			'breadcrumbLabel' => 'test',
			'icon'            => null,
			'id'              => 'test',
			'label'           => 'test',
			'link'            => 'test',
			'search'          => null,
			'title'           => 'test',
		];

		$this->assertSame($expected, $area->toView());
	}

}
