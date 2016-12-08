<?php
declare(strict_types = 1);
/**
 * Contains class LogWiring.
 *
 * PHP version 7.0+
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Yapeal\Container\ContainerInterface;
use Yapeal\Log\Logger;

/**
 * Class LogWiring.
 */
class LogWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     */
    public function wire(ContainerInterface $dic)
    {
        $this->wireLineFormatter($dic)
            ->wireStdErr($dic)
            ->wireLogDir($dic)
            ->wireGroup($dic)
            ->wireStrategy($dic)
            ->wireFingersCrossed($dic)
            ->wireLogger($dic);
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $mediator->addServiceListener('Yapeal.Log.log', ['Yapeal.Log.Callable.Logger', 'logEvent'], 'last');
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireFingersCrossed(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.FingersCrossed'])) {
            $dic['Yapeal.Log.Callable.FingersCrossed'] = function () use ($dic) {
                /**
                 * @var string                $activationStrategy
                 * @var FingersCrossedHandler $fch
                 * @var string                $handler
                 * @var array                 $parameters
                 */
                $activationStrategy = $dic['Yapeal.Log.Parameters.FingersCrossed.activationStrategy'];
                $handler = $dic['Yapeal.Log.Parameters.FingersCrossed.handler'];
                $parameters = [
                    $dic[$handler],
                    $dic[$activationStrategy],
                    $dic['Yapeal.Log.Parameters.FingersCrossed.bufferSize'],
                    $dic['Yapeal.Log.Parameters.FingersCrossed.bubble'],
                    $dic['Yapeal.Log.Parameters.FingersCrossed.stopBuffering'],
                    $dic['Yapeal.Log.Parameters.FingersCrossed.passThruLevel'],
                ];
                $fch = new $dic['Yapeal.Log.Classes.fingersCrossed'](...$parameters);
                return $fch;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireGroup(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.Group'])) {
            $dic['Yapeal.Log.Callable.Group'] = function () use ($dic) {
                $handlers = [];
                foreach (explode(',', $dic['Yapeal.Log.Parameters.Group.handlers']) as $handler) {
                    if ('' === $handler) {
                        continue;
                    }
                    $handlers[] = $dic[$handler];
                }
                $parameters = [
                    $handlers,
                    $bubble = $dic['Yapeal.Log.Parameters.Group.bubble']
                ];
                return new $dic['Yapeal.Log.Classes.group'](...$parameters);
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireLineFormatter(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.LineFormatter'])) {
            $dic['Yapeal.Log.Callable.LineFormatter'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Log.Parameters.LineFormatter.format'],
                    $dic['Yapeal.Log.Parameters.LineFormatter.dateFormat'],
                    $dic['Yapeal.Log.Parameters.LineFormatter.allowInlineLineBreaks'],
                    $dic['Yapeal.Log.Parameters.LineFormatter.ignoreEmptyContextAndExtra']
                ];
                /**
                 * @var \Yapeal\Log\LineFormatter $lineFormatter
                 */
                $lineFormatter = new $dic['Yapeal.Log.Classes.lineFormatter'](...$parameters);
                $lineFormatter->includeStacktraces($dic['Yapeal.Log.Parameters.LineFormatter.includeStackTraces']);
                return $lineFormatter;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireLogDir(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Log.Callable.LogDir'])) {
            $dic['Yapeal.Log.Callable.LogDir'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Log.Parameters.LogDir.stream'],
                    $dic['Yapeal.Log.Parameters.LogDir.level'],
                    $dic['Yapeal.Log.Parameters.LogDir.bubble'],
                    $dic['Yapeal.Log.Parameters.LogDir.filePermission'],
                    $dic['Yapeal.Log.Parameters.LogDir.useLocking']
                ];
                /**
                 * @var StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Log.Classes.stream'](...$parameters);
                $formatter = new $dic[$dic['Yapeal.Log.Parameters.LogDir.lineFormatter']];
                $stream->setFormatter($formatter);
                return $stream;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireLogger(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Log.Callable.Logger'])) {
            $dic['Yapeal.Log.Callable.Logger'] = function () use ($dic) {
                /**
                 * @var Logger $logger
                 */
                $handlers = [];
                foreach (explode(',', $dic['Yapeal.Log.Parameters.Logger.handlers']) as $handler) {
                    if ('' === $handler) {
                        continue;
                    }
                    $handlers[] = $dic[$handler];
                }
                $processors = [];
                foreach (explode(',', $dic['Yapeal.Log.Parameters.Logger.processors']) as $processor) {
                    if ('' === $processor) {
                        continue;
                    }
                    $processors[] = $dic[$processor];
                }
                $parameters = [
                    $dic['Yapeal.Log.Parameters.Logger.name'],
                    $handlers,
                    $processors
                ];
                $logger = new $dic['Yapeal.Log.Classes.logger'](...$parameters);
                return $logger;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireStdErr(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Log.Callable.StdErr'])) {
            $dic['Yapeal.Log.Callable.StdErr'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Log.Parameters.StdErr.stream'],
                    $dic['Yapeal.Log.Parameters.StdErr.level'],
                    $dic['Yapeal.Log.Parameters.StdErr.bubble'],
                    $dic['Yapeal.Log.Parameters.StdErr.filePermission'],
                    $dic['Yapeal.Log.Parameters.StdErr.useLocking']
                ];
                /**
                 * @var StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Log.Classes.stream'](...$parameters);
                $formatter = new $dic[$dic['Yapeal.Log.Parameters.LogDir.lineFormatter']];
                $stream->setFormatter($formatter);
                return $stream;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireStrategy(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.Strategy'])) {
            $dic['Yapeal.Log.Callable.Strategy'] = function () use ($dic) {
                return new $dic['Yapeal.Log.Classes.strategy']((int)$dic['Yapeal.Log.Parameters.Strategy.actionLevel']);
            };
        }
        return $this;
    }
}
