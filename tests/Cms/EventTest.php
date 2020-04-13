<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Event
 */
class EventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::action
     * @covers ::arguments
     * @covers ::name
     * @covers ::state
     * @covers ::type
     */
    public function testConstruct()
    {
        $args = ['arg1' => 'Arg1', 'arg2' => 123];

        // event with full name
        $event = new Event('page.create:after', $args);
        $this->assertSame('page.create:after', $event->name());
        $this->assertSame('page', $event->type());
        $this->assertSame('create', $event->action());
        $this->assertSame('after', $event->state());
        $this->assertSame($args, $event->arguments());

        // event with multiple dots in the name
        $event = new Event('superwoman.plugin.event:before', $args);
        $this->assertSame('superwoman.plugin.event:before', $event->name());
        $this->assertSame('superwoman.plugin', $event->type());
        $this->assertSame('event', $event->action());
        $this->assertSame('before', $event->state());
        $this->assertSame($args, $event->arguments());

        // event without action
        $event = new Event('route:before', $args);
        $this->assertSame('route:before', $event->name());
        $this->assertSame('route', $event->type());
        $this->assertNull($event->action());
        $this->assertSame('before', $event->state());
        $this->assertSame($args, $event->arguments());

        // event without state
        $event = new Event('page.create', $args);
        $this->assertSame('page.create', $event->name());
        $this->assertSame('page', $event->type());
        $this->assertSame('create', $event->action());
        $this->assertNull($event->state());
        $this->assertSame($args, $event->arguments());

        // event with a simple name
        $event = new Event('testEvent', $args);
        $this->assertSame('testEvent', $event->name());
        $this->assertSame('testEvent', $event->type());
        $this->assertNull($event->action());
        $this->assertNull($event->state());
        $this->assertSame($args, $event->arguments());

        // wildcard event
        $event = new Event('page.*:after', $args);
        $this->assertSame('page.*:after', $event->name());
        $this->assertSame('page', $event->type());
        $this->assertSame('*', $event->action());
        $this->assertSame('after', $event->state());
        $this->assertSame($args, $event->arguments());
    }

    /**
     * @covers ::__call
     * @covers ::argument
     */
    public function testArgument()
    {
        $event = new Event('page.create:after', ['arg1' => 'Arg1', 'arg2' => 123]);

        $this->assertSame('Arg1', $event->argument('arg1'));
        $this->assertSame('Arg1', $event->arg1());
        $this->assertSame(123, $event->argument('arg2'));
        $this->assertSame(123, $event->arg2());

        $this->assertNull($event->argument('arg3'));
        $this->assertNull($event->arg3());
    }

    /**
     * @covers ::call
     */
    public function testCall()
    {
        $self     = $this;
        $eventObj = new Event('page.create:after', ['arg1' => 'Arg1', 'arg2' => 123]);

        // without bound object
        $result = $eventObj->call(null, function ($arg2, $event, $arg3, $arg1) use ($eventObj, $self) {
            $self->assertSame('Arg1', $arg1);
            $self->assertSame(123, $arg2);
            $self->assertNull($arg3);
            $self->assertSame($eventObj, $event);
            $self->assertSame($self, $this);

            return 'some return value';
        });
        $this->assertSame('some return value', $result);

        // with bound object
        $result = $eventObj->call($eventObj, function ($arg2, $event, $arg3, $arg1) use ($eventObj, $self) {
            $self->assertSame('Arg1', $arg1);
            $self->assertSame(123, $arg2);
            $self->assertNull($arg3);
            $self->assertSame($eventObj, $event);
            $self->assertSame($eventObj, $this);

            return 'another value';
        });
        $this->assertSame('another value', $result);
    }

    /**
     * @covers ::nameWildcards
     */
    public function testNameWildcards()
    {
        // event with full name
        $event = new Event('page.create:after', []);
        $this->assertSame([
            'page.*:after',
            'page.create:*',
            'page.*:*',
            '*.create:after',
            '*.create:*',
            '*:after',
            '*'
        ], $event->nameWildcards());

        // event without action
        $event = new Event('route:before', []);
        $this->assertSame([
            'route:*',
            '*:before',
            '*'
        ], $event->nameWildcards());

        // event without state
        $event = new Event('page.create', []);
        $this->assertSame([
            'page.*',
            '*.create',
            '*'
        ], $event->nameWildcards());

        // event with a simple name
        $event = new Event('testEvent', []);
        $this->assertSame([
            '*'
        ], $event->nameWildcards());

        // type wildcard event
        $event = new Event('*.create:after', []);
        $this->assertSame([], $event->nameWildcards());

        // action wildcard event
        $event = new Event('page.*:after', []);
        $this->assertSame([], $event->nameWildcards());

        // state wildcard event
        $event = new Event('page.create:*', []);
        $this->assertSame([], $event->nameWildcards());

        // wildcard event without action 1
        $event = new Event('*:after', []);
        $this->assertSame([], $event->nameWildcards());

        // wildcard event without action 2
        $event = new Event('page:*', []);
        $this->assertSame([], $event->nameWildcards());

        // wildcard event without state 1
        $event = new Event('*.create', []);
        $this->assertSame([], $event->nameWildcards());

        // wildcard event without state 2
        $event = new Event('page.*', []);
        $this->assertSame([], $event->nameWildcards());

        // wildcard event with a simple name
        $event = new Event('*', []);
        $this->assertSame([], $event->nameWildcards());
    }

    /**
     * @covers ::__debugInfo
     * @covers ::__toString
     * @covers ::toArray
     * @covers ::toString
     */
    public function testExport()
    {
        $name       = 'page.create:after';
        $arguments  = ['arg1' => 'Arg1', 'arg2' => 123];
        $event      = new Event($name, $arguments);

        $this->assertSame($name, $event->toString());
        $this->assertSame($name, (string)$event);

        $this->assertSame(compact('name', 'arguments'), $event->toArray());
        $this->assertSame(compact('name', 'arguments'), $event->__debugInfo());
    }

    /**
     * @covers ::updateArgument
     */
    public function testUpdateArgument()
    {
        $event = new Event('page.create:after', ['arg1' => 'Arg1', 'arg2' => 123]);

        $this->assertSame('Arg1', $event->arg1());
        $event->updateArgument('arg1', 'New Arg1');
        $this->assertSame('New Arg1', $event->arg1());

        $this->assertSame(123, $event->arg2());
        $event->updateArgument('arg2', 456);
        $this->assertSame(456, $event->arg2());
    }

    /**
     * @covers ::updateArgument
     */
    public function testUpdateArgumentDoesNotExist()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The argument arg3 does not exist');

        $event = new Event('page.create:after', ['arg1' => 'Arg1', 'arg2' => 123]);

        $event->updateArgument('arg3', 'New Arg3');
    }
}
