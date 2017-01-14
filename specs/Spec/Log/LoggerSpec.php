<?php
declare(strict_types = 1);
/**
 * Contains class LoggerSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016-2017 Michael Cummings
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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0
 */
namespace Spec\Yapeal\Log;

use Monolog\Handler\HandlerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Yapeal\Event\LogEventInterface;
use Yapeal\Log\Logger;

/**
 * Class LoggerSpec
 *
 * @mixin \Yapeal\Log\Logger
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class LoggerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Logger::class);
    }
    public function it_should_allow_getting_level_name_statically()
    {
        $this::getLevelName(Logger::ERROR)
            ->shouldReturn('ERROR');
    }
    /**
     * @param HandlerInterface|\PhpSpec\Wrapper\Collaborator  $handler
     * @param \PhpSpec\Wrapper\Collaborator|LogEventInterface $event
     *
     * @throws \Prophecy\Exception\InvalidArgumentException
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function it_should_log_the_given_event_in_log_event(HandlerInterface $handler, LogEventInterface $event)
    {
        $event->getLevel()
            ->willReturn(Logger::WARNING);
        $event->getMessage()
            ->willReturn('test');
        $event->getContext()
            ->willReturn([]);
        /** @noinspection PhpParamsInspection */
        $handler->handle(Argument::type('array'))
            ->willReturn(true);
        /** @noinspection PhpParamsInspection */
        $handler->isHandling(Argument::type('array'))
            ->willReturn(true);
        $this->beConstructedWith(__METHOD__, [$handler]);
        $this->logEvent($event);
    }
    public function let()
    {
        $this->beConstructedWith('yapeal');
    }
}
