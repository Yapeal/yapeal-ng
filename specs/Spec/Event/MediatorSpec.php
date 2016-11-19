<?php
declare(strict_types = 1);
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
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Yapeal\Container\Container;

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
     * @param Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_add_listener(MockListener $listener)
    {
        $this->addListener('test', [$listener, 'method1'])
            ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_add_service_listener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1'])
            ->shouldReturn($this);
    }
    /**
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_add_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub)
            ->shouldReturn($this);
    }
    /**
     * @param Collaborator|MockSubscriber $sub
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
     * @param Collaborator|MockListener $listener
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_remove_listener(MockListener $listener)
    {
        $this->removeListener('test', [$listener, 'method1'])
            ->shouldReturn($this);
    }
    public function it_provides_fluent_interface_from_remove_service_listener()
    {
        $this->removeServiceListener('test', ['\DummyClass', 'method1'])
            ->shouldReturn($this);
    }
    /**
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_provides_fluent_interface_from_remove_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->removeServiceSubscriber($sub)
            ->shouldReturn($this);
    }
    /**
     * @param Collaborator|MockSubscriber $sub
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
    public function it_returns_empty_array_before_any_service_listeners_added()
    {
        $this->getServiceListeners()
            ->shouldHaveCount(0);
    }
    /**
     * @param Collaborator|MockListener $listener
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
    public function it_returns_empty_array_when_event_has_no_service_listeners()
    {
        $this->addServiceListener('test2', ['ContainerID1', 'method1'])
            ->getServiceListeners('test1')
            ->shouldHaveCount(0);
    }
    /**
     * @param Collaborator|MockSubscriber $sub
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
     * @param Collaborator|MockListener $listener
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
     * @param Collaborator|MockListener $listener
     * @param Event|Collaborator        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
     * @param Collaborator|MockListener $listener
     * @param Event|Collaborator        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
    /**
     * @param Collaborator|MockListener $listener
     * @param Event|Collaborator        $event
     * @param Collaborator|Container    $container
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function it_should_call_service_listeners_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event,
        Container $container
    ) {
        $event->hasBeenHandled()
            ->willReturn(false);
        $this->addServiceListener('test1', ['ContainerID1', 'method1']);
        $this->getServiceListeners()
            ->shouldHaveKey('test1');
        $container->offsetGet('ContainerID1')
            ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
            ->shouldBeCalled();
        $this->trigger('test1', $event);
    }
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * @param Collaborator|MockListener          $listener
     * @param Event|Collaborator                 $event
     * @param Collaborator|Container             $container
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function it_should_call_service_subscribers_for_their_events_when_event_is_triggered(
        MockListener $listener,
        Event $event,
        Container $container,
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $event->hasBeenHandled()
            ->willReturn(false);
        $listener->method1($event, 'test1', $this)
            ->willReturn($event);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldHaveKey('test1');
        $container->offsetGet('containerID1')
            ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
            ->shouldBeCalled();
        $this->getServiceByName('containerID1')
            ->shouldReturn($listener);
        $this->trigger('test1', $event);
    }
    public function it_should_get_the_same_event_back_from_trigger_if_there_are_no_listeners()
    {
        $event = new Event();
        $this->trigger('test', $event)
            ->shouldReturn($event);
    }
    /**
     * @param Collaborator|MockListener $listener
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
    public function it_should_have_less_service_listeners_if_one_is_removed()
    {
        $listeners = [
            ['event1', 'containerID1', 'method1', 0],
            ['event1', 'containerID1', 'method1', 'first'],
            ['event2', 'containerID1', 'method1', 0]
        ];
        foreach ($listeners as $listener) {
            list($event, $containerID, $method, $priority) = $listener;
            $this->addServiceListener($event, [$containerID, $method], $priority);
        }
        $this->getServiceListeners()
            ->shouldHaveCount(2);
        $this->getServiceListeners()
            ->shouldHaveKey('event1');
        $this->getServiceListeners()
            ->shouldHaveKey('event2');
        $this->getServiceListeners('event1')
            ->shouldHaveCount(2);
        $this->removeServiceListener('event1', ['containerID1', 'method1'], 'first');
        $this->getServiceListeners('event1')
            ->shouldHaveCount(1);
        $this->removeServiceListener('event1', ['containerID1', 'method1']);
        $this->getServiceListeners('event1')
            ->shouldHaveCount(0);
        $this->getServiceListeners()
            ->shouldHaveCount(1);
    }
    /**
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_have_listener_after_adding_service_subscriber(MockServiceSubscriber $sub)
    {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test2' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ]
        ];
        $this->getServiceListeners()
            ->shouldHaveCount(0);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldHaveCount(2);
        $this->getServiceListeners()
            ->shouldHaveKey('test1');
        $this->getServiceListeners()
            ->shouldHaveKey('test2');
    }
    /**
     * @param Collaborator|MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
     * @param Collaborator|MockSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \Prophecy\Exception\InvalidArgumentException
     */
    public function it_should_have_no_service_listeners_if_only_service_subscriber_is_removed(
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ],
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test2' => [
                'last' => [
                    [
                        'containerID1',
                        'method1'
                    ]
                ],
                [
                    [
                        'containerID1',
                        'method2'
                    ]
                ]
            ],
            'test3' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldHaveCount(3);
        $this->removeServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldHaveCount(0);
    }
    /**
     * @param Collaborator|MockListener $listener
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
    public function it_should_ignore_duplicate_service_listeners_for_the_same_event_and_priority()
    {
        $this->addServiceListener('event',
            ['\Spec\EventMediator\MockListener', 'method1']);
        $this->addServiceListener('event',
            ['\Spec\EventMediator\MockListener', 'method1']);
        $this->getServiceListeners('event')
            ->shouldHaveCount(1);
    }
    /**
     * @param Collaborator|MockListener $listener1
     * @param Collaborator|MockListener $listener2
     * @param Event|Collaborator        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
     * @param Collaborator|MockListener $listener
     * @param Event|Collaborator        $event
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \Prophecy\Exception\InvalidArgumentException
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
     * @param Collaborator|MockListener $listener
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
     * @param Collaborator|MockListener $listener
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
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * @param Collaborator|MockListener          $listener
     * @param Event|Collaborator                 $event
     * @param Collaborator|Container             $container
     * @param Collaborator|MockServiceSubscriber $sub
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LengthException
     * @throws \LogicException
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function it_should_still_allow_service_subscriber_to_be_removed_after_event_has_been_triggered(
        MockListener $listener,
        Event $event,
        Container $container,
        MockServiceSubscriber $sub
    ) {
        $events = [
            'test1' => [
                [
                    [
                        'containerID1',
                        'method1'
                    ]
                ]
            ]
        ];
        $event->hasBeenHandled()
            ->willReturn(false);
        $sub->getServiceSubscribedEvents()
            ->willReturn($events);
        $this->addServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldHaveKey('test1');
        $container->offsetGet('containerID1')
            ->willReturn($listener);
        $this->setServiceContainer($container);
        $listener->method1($event, 'test1', $this)
            ->shouldBeCalled();
        $this->getServiceByName('containerID1')
            ->shouldReturn($listener);
        $this->trigger('test1', $event);
        $this->removeServiceSubscriber($sub);
        $this->getServiceListeners()
            ->shouldNotHaveKey('test1');
    }
    public function it_still_returns_an_event_from_trigger_even_if_none_given()
    {
        $this->trigger('test', null)
            ->shouldReturnAnInstanceOf('EventMediator\EventInterface');
    }
    public function it_throws_exception_for_badly_formed_listener_when_trying_to_add_service_listener()
    {
        $mess = 'Service listener form MUST be ["containerID", "methodName"]';
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('addServiceListener', ['test', ['methodName']]);
    }
    /**
     * @param Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_empty_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('addListener', ['', [$listener, 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_adding_service_listener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('addServiceListener', ['', ['\DummyClass', 'method1']]);
    }
    /**
     * @param Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_empty_event_name_when_removing_listener(MockListener $listener)
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('removeListener', ['', [$listener, 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_removing_service_listener()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('removeServiceListener', ['', ['\DummyClass', 'method1']]);
    }
    public function it_throws_exception_for_empty_event_name_when_triggered()
    {
        $mess = 'Event name can NOT be empty';
        $this->shouldThrow(new \DomainException($mess))
            ->during('trigger', ['']);
    }
    public function it_throws_exception_for_empty_listener_class_name_when_trying_to_add_service_listener()
    {
        $mess = 'Using any non-printable characters in the container ID is NOT allowed';
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('addServiceListener', ['test', ['', 'test1']]);
    }
    public function it_throws_exception_for_empty_listener_method_name_when_trying_to_add_service_listener()
    {
        $mess = 'Service listener method name format is invalid, was given ';
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('addServiceListener', ['test', ['\DummyClass', '']]);
    }
    /**
     * @param Collaborator|MockSubscriber $sub
     */
    public function it_throws_exception_for_incorrect_service_container_type_when_trying_to_set_container(
        MockSubscriber $sub
    ) {
        $mess = sprintf('Must be an instance of ContainerInterface but was given %s',
            gettype($sub));
        $this->shouldThrow(new \InvalidArgumentException($mess))
            ->during('setServiceContainer', [$sub]);
    }
    /**
     * @param Collaborator|MockListener $listener
     */
    public function it_throws_exception_for_invalid_event_name_when_adding_listener(MockListener $listener)
    {
        $mess = 'Using any non-printable characters in the event name is NOT allowed';
        $this->shouldThrow(new \DomainException($mess))
            ->during('addListener', ["\001", [$listener, 'method1']]);
    }
    public function it_throws_exception_for_invalid_listener_types_when_trying_to_add_service_listener()
    {
        $listeners = [
            [123, 'method1'],
            [true, 'method1'],
            [null, 'method1']
        ];
        $mess = 'Service listener container ID MUST be a string, but was given ';
        foreach ($listeners as $listener) {
            list($class, $method) = $listener;
            $this->shouldThrow(new \InvalidArgumentException($mess . gettype($class)))
                ->during('addServiceListener', ['test', [$class, $method]]);
        }
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
    public function it_throws_exception_for_non_string_listener_method_name_when_trying_to_add_service_listener()
    {
        $messages = [
            'array' => [],
            'integer' => 0,
            'NULL' => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but was given ' . $mess;
            $this->shouldThrow(new \InvalidArgumentException($mess))
                ->during('addServiceListener', ['test', ['\DummyClass', $methodName]]);
        }
    }
    public function it_throws_exception_for_non_string_listener_method_name_when_trying_to_remove_service_listener()
    {
        $this->addServiceListener('test', ['\DummyClass', 'method1']);
        $messages = [
            'array' => [],
            'integer' => 0,
            'NULL' => null
        ];
        foreach ($messages as $mess => $methodName) {
            $mess = 'Service listener method name MUST be a string, but was given ' . $mess;
            $this->shouldThrow(new \InvalidArgumentException($mess))
                ->during('removeServiceListener', ['test', ['\DummyClass', $methodName]]);
        }
    }
}
