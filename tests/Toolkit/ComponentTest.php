<?php

namespace Kirby\Toolkit;

class ComponentTest extends TestCase
{

    public function tearDown()
    {
        Component::$types = [];
    }

    public function testProp()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'prop' => function ($prop) {
                        return $prop;
                    }
                ]
            ]
        ];

        $component = new Component('test', ['prop' => 'prop value']);

        $this->assertEquals('prop value', $component->prop());
        $this->assertEquals('prop value', $component->prop);
    }

    public function testPropWithDefaultValue()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'prop' => function ($prop = 'default value') {
                        return $prop;
                    }
                ]
            ]
        ];

        $component = new Component('test');

        $this->assertEquals('default value', $component->prop());
        $this->assertEquals('default value', $component->prop);
    }

    public function testComputed()
    {
        Component::$types = [
            'test' => [
                'computed' => [
                    'prop' => function () {
                        return 'computed prop';
                    }
                ]
            ]
        ];

        $component = new Component('test');

        $this->assertEquals('computed prop', $component->prop());
        $this->assertEquals('computed prop', $component->prop);
    }

    public function testComputedFromProp()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'prop' => function ($prop) {
                        return $prop;
                    }
                ],
                'computed' => [
                    'prop' => function () {
                        return 'computed: ' . $this->prop;
                    }
                ]
            ]
        ];

        $component = new Component('test', ['prop' => 'prop value']);

        $this->assertEquals('computed: prop value', $component->prop());
    }

    public function testMethod()
    {
        Component::$types = [
            'test' => [
                'methods' => [
                    'say' => function () {
                        return 'hello world';
                    }
                ]
            ]
        ];

        $component = new Component('test');

        $this->assertEquals('hello world', $component->say());
    }

    public function testPropsInMethods()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'message' => function ($message) {
                        return $message;
                    }
                ],
                'methods' => [
                    'say' => function () {
                        return $this->message;
                    }
                ]
            ]
        ];

        $component = new Component('test', ['message' => 'hello world']);

        $this->assertEquals('hello world', $component->say());
    }

    public function testComputedPropsInMethods()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'message' => function ($message) {
                        return $message;
                    }
                ],
                'computed' => [
                    'message' => function () {
                        return strtoupper($this->message);
                    },
                ],
                'methods' => [
                    'say' => function () {
                        return $this->message;
                    }
                ]
            ]
        ];

        $component = new Component('test', ['message' => 'hello world']);

        $this->assertEquals('HELLO WORLD', $component->say());
    }

    public function testToArray()
    {
        Component::$types = [
            'test' => [
                'props' => [
                    'message' => function ($message) {
                        return $message;
                    }
                ],
                'computed' => [
                    'message' => function () {
                        return strtoupper($this->message);
                    },
                ],
                'methods' => [
                    'say' => function () {
                        return $this->message;
                    }
                ]
            ]
        ];

        $component = new Component('test', ['message' => 'hello world']);

        $this->assertEquals(['message' => 'HELLO WORLD'], $component->toArray());
    }

    public function testCustomToArray()
    {
        Component::$types = [
            'test' => [
                'toArray' => function () {
                    return [
                        'foo' => 'bar'
                    ];
                }
            ]
        ];

        $component = new Component('test');

        $this->assertEquals(['foo' => 'bar'], $component->toArray());
    }

}
