<?php

namespace Kirby\Cms;

use ReflectionClass;
use Kirby\Data\Data;

class BlueprintTestCase extends TestCase
{

    public $blueprint = null;

    public function blueprint()
    {
        return Data::read($this->blueprintRoot());
    }

    public function blueprintRoot(): string
    {
        return $this->kirby()->root('blueprints') . '/' . $this->blueprintName() . '.yml';
    }

    public function blueprintName(): string
    {
        if ($this->blueprint !== null) {
            return $this->blueprint;
        }

        $reflect   = new ReflectionClass($this);
        $className = $reflect->getShortName();

        return strtolower(str_replace('BlueprintTest', '', $className));
    }

    public function blueprintSetting(string $key, $default = null)
    {
        return $this->blueprint()[$key] ?? $default;
    }

    public function blueprintTabs(): array
    {
        return $this->blueprintSetting('tabs', []);
    }

    public function blueprintTab(string $name)
    {
        foreach ($this->blueprintTabs() as $tab) {
            if (($tab['name'] ?? null) === $name) {
                return $tab;
            }
        }

        return null;
    }

    public function assertBlueprintTitle(string $title)
    {
        return $this->assertEquals($title, $this->blueprintSetting('title'));
    }

    public function assertBlueprintHasSetting($key)
    {
        $this->assertNotNull($this->blueprintSetting($key));
    }

    public function assertBlueprintHasTab(string $name)
    {
        $tab = $this->blueprintTab($name);

        $this->assertTrue(is_array($tab));
        $this->assertEquals($name, $tab['name']);
    }

    public function testBlueprintExists()
    {
        $this->assertFileExists($this->blueprintRoot());
    }

    public function testBlueprintCanBeLoaded()
    {
        $this->assertTrue(is_array($this->blueprint()));
    }

    public function testBlueprintHasTitle()
    {
        $this->assertBlueprintHasSetting('title');
    }

    public function testBlueprintHasTabs()
    {
        $this->assertBlueprintHasSetting('tabs');
    }

}
