<?php
declare(strict_types=1);
/**
 * Contains Mediator class.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\AbstractContainerMediator;
use EventMediator\ContainerMediatorInterface;
use EventMediator\EventInterface;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/** @noinspection LongInheritanceChainInspection */
/**
 * Class Mediator
 */
class Mediator extends AbstractContainerMediator implements MediatorInterface
{
    /**
     * @param ContainerInterface|null $serviceContainer
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(ContainerInterface $serviceContainer = null)
    {
        $this->setServiceContainer($serviceContainer);
    }
    /**
     * This is used to bring in the service container that will be used.
     *
     * Though not required it would be considered best practice for this method
     * to create a new instance of the container when given null. Another good
     * practice is to call this method from the class constructor to allow
     * easier testing.
     *
     * @param ContainerInterface|null $value
     *
     * @return ContainerMediatorInterface Fluent interface.
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @link http://pimple.sensiolabs.org/ Pimple
     */
    public function setServiceContainer($value = null): ContainerMediatorInterface
    {
        if (null === $value) {
            $value = new Container();
        }
        if (!$value instanceof ContainerInterface) {
            $mess = sprintf('Must be an instance of ContainerInterface but was given %s',
                gettype($value));
            throw new \InvalidArgumentException($mess);
        }
        $this->serviceContainer = $value;
        return $this;
    }
    /**
     * @param string                   $eventName
     * @param EveApiReadWriteInterface $data
     * @param EveApiEventInterface     $event
     *
     * @return EventInterface|EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerEveApiEvent(
        $eventName,
        EveApiReadWriteInterface $data,
        EveApiEventInterface $event = null
    ): EventInterface {
        if (null === $event) {
            $event = new EveApiEvent();
        }
        $event->setData($data);
        return $this->trigger($eventName, $event);
    }
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string            $eventName
     * @param mixed             $level
     * @param string            $message
     * @param array             $context
     * @param LogEventInterface $event
     *
     * @return EventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerLogEvent(
        $eventName,
        $level = Logger::DEBUG,
        $message = '',
        array $context = [],
        LogEventInterface $event = null
    ): EventInterface {
        if (null === $event) {
            $event = new LogEvent();
        }
        $event->setLevel($level)
            ->setMessage($message)
            ->setContext($context);
        return $this->trigger($eventName, $event);
    }
    /** @noinspection GenericObjectTypeUsageInspection */
    /**
     * This method is used any time the mediator need to get the actual instance
     * of the class for an event.
     *
     * Normal will only be called during actual trigger of an event since lazy
     * loading is used.
     *
     * @param string $serviceName
     *
     * @return object
     * @throws \LogicException
     */
    public function getServiceByName(string $serviceName)
    {
        return $this->getServiceContainer()[$serviceName];
    }
    /**
     * Used to get the service container.
     *
     * @return ContainerInterface|null
     */
    private function getServiceContainer()
    {
        return $this->serviceContainer;
    }
    /**
     * Holds the container instance to be used.
     *
     * @var ContainerInterface|null $serviceContainer
     */
    private $serviceContainer;
}
