<?php
declare(strict_types=1);
/**
 * Contains class MediatorSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0
 */
namespace Spec\Yapeal\Event;

use EventMediator\Event;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class MediatorSpec
 *
 * @mixin \Yapeal\Event\Mediator
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class MediatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\\EventMediator\\Mediator');
        $this->shouldImplement('\\EventMediator\\MediatorInterface');
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_add_listener(MockListener $listener)
    {
        $this->addListener('test', [$listener, 'method1'])
            ->shouldReturn($this);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_add_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub)
            ->shouldReturn($this);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_remove_listener(MockListener $listener)
    {
        $this->removeListener('test', [$listener, 'method1'])
            ->shouldReturn($this);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_remove_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->removeSubscriber($sub)
            ->shouldReturn($this);
    }
    public function it_returns_empty_array_before_any_listeners_added()
    {
        $this->getListeners()
            ->shouldHaveCount(0);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_returns_empty_array_when_event_has_no_listeners(MockListener $listener)
    {
        $this->addListener('test2', [$listener, 'method1'])
            ->getListeners('test1')
            ->shouldHaveCount(0);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_returns_multiple_listener_events_after_adding_multiple_event_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
            ->shouldHaveCount(2);
        $this->getListeners()
            ->shouldHaveKey('test1');
        $this->getListeners()
            ->shouldHaveKey('test2');
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_returns_true_when_event_not_given_but_listeners_exist(MockListener $listener)
    {
        $this->shouldNotHaveListeners();
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'last']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->shouldHaveListeners();
    }
    /**
     * Issue #1 - Mediator calls listeners in wrong order.
     *
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_call_listeners_for_their_events_in_correct_priority_order_when_event_is_triggered(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
            ->willReturn(false);
        $this->addListener('test1', [$listener, 'method1']);
        $this->addListener('test1', [$listener, 'method2']);
        $this->addListener('test1', [$listener, 'method1'], 'first');
        $this->addListener('test1', [$listener, 'method1'], 'last');
        $this->getListeners()
            ->shouldHaveKey('test1');
        $listener->method1($event, 'test1', $this)
            ->shouldBeCalled();
        $listener->method1($event, 'test1', $this)
            ->willReturn($event);
        $listener->method2($event, 'test1', $this)
            ->shouldBeCalled();
        $listener->method2($event, 'test1', $this)
            ->willReturn($event);
        $this->trigger('test1', $event);
        $expected = [
            1 => [[$listener, 'method1']],
            0 => [[$listener, 'method1'], [$listener, 'method2']],
            -1 => [[$listener, 'method1']]
        ];
        $this->getListeners('test1')
            ->shouldReturn($expected);
    }
    /**
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_call_listeners_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
            ->willReturn(false);
        $listener->method1($event, 'test1', $this)
            ->willReturn($event);
        $this->addListener('test1', [$listener, 'method1']);
        $this->getListeners()
            ->shouldHaveKey('test1');
        $listener->method1($event, 'test1', $this)
            ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    public function it_should_get_the_same_event_back_from_trigger_if_there_are_no_listeners()
    {
        $event = new Event();
        $this->trigger('test', $event)
            ->shouldReturn($event);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_have_less_listeners_if_one_is_removed(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 0],
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0]
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners()
            ->shouldHaveCount(2);
        $this->getListeners()
            ->shouldHaveKey('event1');
        $this->getListeners()
            ->shouldHaveKey('event2');
        $this->getListeners('event1')
            ->shouldHaveCount(2);
        $this->removeListener('event1', [$listener, 'method1']);
        $this->getListeners('event1')
            ->shouldHaveCount(1);
        $this->removeListener('event1', [$listener, 'method1'], 'first');
        $this->getListeners('event1')
            ->shouldHaveCount(0);
        $this->getListeners()
            ->shouldHaveCount(1);
    }
    /**
     * @param MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_listener_after_adding_subscriber(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        $sub,
                        'method1'
                    ],
                    [
                        $sub,
                        'method2'
                    ]
                ]
            ]
        ];
        $this->getListeners()
            ->shouldHaveCount(0);
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
            ->shouldHaveCount(1);
        $this->getListeners()
            ->shouldHaveKey('test1');
    }
    /**
     * @param MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */
    public function it_should_have_no_listeners_if_only_subscriber_is_removed(MockSubscriber $sub)
    {
        $events = [
            'test1' => [
                1 => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        $sub,
                        'method1'
                    ]
                ],
                [
                    [
                        $sub,
                        'method2'
                    ]
                ]
            ],
            'test3' => [
                1 => [
                    [
                        $sub,
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getSubscribedEvents()
            ->willReturn($events);
        $this->addSubscriber($sub);
        $this->getListeners()
            ->shouldHaveCount(3);
        $this->removeSubscriber($sub);
        $this->getListeners()
            ->shouldHaveCount(0);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_ignore_duplicate_listeners_for_the_same_event_and_priority(MockListener $listener)
    {
        $this->addListener('event', [$listener, 'method1']);
        $this->addListener('event', [$listener, 'method1']);
        $this->getListeners('event')
            ->shouldHaveCount(1);
    }
    /**
     * @param MockListener $listener1
     * @param MockListener $listener2
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_only_call_listeners_for_current_events_when_event_triggers(
        MockListener $listener1,
        MockListener $listener2,
        Event $event
    ) {
        $this->addListener('test1', [$listener1, 'method1']);
        $this->addListener('test2', [$listener2, 'method2']);
        $this->getListeners()
            ->shouldHaveKey('test1');
        $this->getListeners()
            ->shouldHaveKey('test2');
        $event->hasBeenHandled()
            ->willReturn(true);
        /** @noinspection PhpStrictTypeCheckingInspection */
        $listener2->method2(Argument::type('\EventMediator\EventInterface'), Argument::is('test2'), $this)
            ->shouldBeCalled();
        /** @noinspection PhpStrictTypeCheckingInspection */
        $listener1->method1(Argument::type('\EventMediator\EventInterface'),
            Argument::any(),
            Argument::type('\EventMediator\MediatorInterface'))
            ->shouldNotBeCalled();
        $this->trigger('test2', $event);
    }
    /**
     * Issue #2 - Higher priority handles don't stop lower priority listeners from seeing event.
     *
     * @param MockListener $listener
     * @param Event        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_only_call_listeners_for_event_until_one_of_them_handles_the_event(
        MockListener $listener,
        Event $event
    ) {
        $event->hasBeenHandled()
            ->willReturn(true)
            ->shouldBeCalled();
        $listener->method2($event, 'test1', $this)
            ->willReturn($event);
        $this->addListener('test1', [$listener, 'method2']);
        $this->addListener('test1', [$listener, 'method1'], 'last');
        $this->getListeners()
            ->shouldHaveKey('test1');
        $expected = [0 => [[$listener, 'method2']], -1 => [[$listener, 'method1']]];
        $this->getListeners('test1')
            ->shouldReturn($expected);
        $listener->method2($event, 'test1', $this)
            ->shouldBeCalled();
        $listener->method1($event, 'test1', $this)
            ->shouldNotBeCalled();
        $this->trigger('test1', $event);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_return_all_listeners_if_event_name_is_empty(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'first']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners()
            ->shouldHaveCount(2);
        $this->getListeners()
            ->shouldHaveKey('event1');
        $this->getListeners()
            ->shouldHaveKey('event2');
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_should_return_only_listeners_for_the_event_requested(MockListener $listener)
    {
        $listeners = [
            ['event1', $listener, 'method1', 'first'],
            ['event2', $listener, 'method1', 0],
            ['event2', $listener, 'method1', 'last']
        ];
        foreach ($listeners as $aListener) {
            list($event, $object, $method, $priority) = $aListener;
            $this->addListener($event, [$object, $method], $priority);
        }
        $this->getListeners('event1')
            ->shouldHaveCount(1);
        $this->getListeners('event1')
            ->shouldHaveKey(1);
        $this->getListeners('event2')
            ->shouldHaveCount(2);
        $this->getListeners('event2')
            ->shouldHaveKey(0);
        $this->getListeners('event2')
            ->shouldHaveKey(-1);
    }
    public function it_still_returns_an_event_from_trigger_even_if_none_given()
    {
        $this->trigger('test', null)
            ->shouldReturnAnInstanceOf('EventMediator\EventInterface');
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_empty_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('addListener', ['', [$listener, 'method1']]);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_empty_event_name_when_removing_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('removeListener', ['', [$listener, 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_triggered()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('trigger', ['']);
    }
    /**
     * @param \PhpSpec\Wrapper\Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_invalid_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Using any non-printable characters in the event name is NOT allowed';
        $this->shouldThrow(new \DomainException($mess))
            ->during('addListener', ["\001", [$listener, 'method1']]);
    }
    public function it_throws_exception_for_missing_listeners_when_add_listeners_by_event_list()
    {
        $events = [
            'test1' => [0]
        ];
        $mess = 'Must have at least one listener per listed priority';
        $this->shouldThrow(new \LengthException($mess))
            ->during('addListenersByEventList', [$events]);
        $events = [
            'test1' => [0 => []]
        ];
        $mess = 'Must have at least one listener per listed priority';
        $this->shouldThrow(new \LengthException($mess))
            ->during('addListenersByEventList', [$events]);
    }
    public function it_throws_exception_for_missing_priorities_when_add_listeners_by_event_list()
    {
        $events = [
            'test1'
        ];
        $mess = 'Must have as least one priority per listed event';
        $this->shouldThrow(new \LengthException($mess))
            ->during('addListenersByEventList', [$events]);
        $events = [
            'test1' => []
        ];
        $mess = 'Must have as least one priority per listed event';
        $this->shouldThrow(new \LengthException($mess))
            ->during('addListenersByEventList', [$events]);
    }
}
