<?php
declare(strict_types = 1);
/**
 * Contains Mediator class.
 *
 * PHP version 7.0+
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\AbstractContainerMediator;
use EventMediator\ContainerMediatorInterface;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

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
     * @param string                    $eventName
     * @param EveApiReadWriteInterface  $data
     * @param EveApiEventInterface|null $event
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function triggerEveApiEvent(
        string $eventName,
        EveApiReadWriteInterface $data,
        EveApiEventInterface $event = null
    ): EveApiEventInterface {
        if (null === $event) {
            $event = new EveApiEvent();
        }
        $event->setData($data);
        $event = $this->trigger($eventName, $event);
        if (!$event instanceof EveApiEventInterface) {
            $mess = 'Received un-expected EventInterface from trigger';
            throw new \UnexpectedValueException($mess);
        }
        return $event;
    }
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * @param string                 $eventName
     * @param int                    $level
     * @param string                 $message
     * @param array                  $context
     * @param LogEventInterface|null $event
     *
     * @return LogEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function triggerLogEvent(
        string $eventName,
        int $level = Logger::DEBUG,
        string $message = '',
        array $context = [],
        LogEventInterface $event = null
    ): LogEventInterface {
        if (null === $event) {
            $event = new LogEvent();
        }
        $event->setLevel($level)
            ->setMessage($message)
            ->setContext($context);
        $event = $this->trigger($eventName, $event);
        if (!$event instanceof LogEventInterface) {
            $mess = 'Received un-expected EventInterface from trigger';
            throw new \UnexpectedValueException($mess);
        }
        return $event;
    }
    /**
     * Used to get the service container.
     *
     * @return ContainerInterface
     * @throws \LogicException
     */
    private function getServiceContainer()
    {
        if (null === $this->serviceContainer) {
            $mess = 'Tried to use serviceContainer before it was set';
            throw new \LogicException($mess);
        }
        return $this->serviceContainer;
    }
    /**
     * Holds the container instance to be used.
     *
     * @var ContainerInterface|null $serviceContainer
     */
    private $serviceContainer;
}
