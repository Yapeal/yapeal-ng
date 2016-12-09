<?php
declare(strict_types = 1);
/**
 * Contains class ErrorWiring.
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

use Yapeal\Container\ContainerInterface;

/**
 * Class ErrorWiring.
 */
class ErrorWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     */
    public function wire(ContainerInterface $dic)
    {
        $this->wireLineFormatter($dic)
            ->wireCli($dic)
            ->wireFileSystem($dic)
            ->wireGroup($dic)
            ->wireStrategy($dic)
            ->wireFingersCrossed($dic)
            ->wireLogger($dic);
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $mediator->addServiceListener('Yapeal.Log.error', ['Yapeal.Error.Callable.Logger', 'logEvent'], 'last');
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireCli(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Error.Callable.Cli'])) {
            $dic['Yapeal.Error.Callable.Cli'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Error.Parameters.Cli.stream'],
                    $dic['Yapeal.Error.Parameters.Cli.level'],
                    $dic['Yapeal.Error.Parameters.Cli.bubble'],
                    $dic['Yapeal.Error.Parameters.Cli.filePermission'],
                    $dic['Yapeal.Error.Parameters.Cli.useLocking']
                ];
                /**
                 * @var \Yapeal\Log\StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Error.Classes.stream'](...$parameters);
                $stream->setPreserve($dic['Yapeal.Error.Parameters.Cli.preserve']);
                $lineFormatter = $dic['Yapeal.Error.Parameters.Cli.lineFormatter'];
                $stream->setFormatter($dic[$lineFormatter]);
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
    private function wireFileSystem(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Error.Callable.FileSystem'])) {
            $dic['Yapeal.Error.Callable.FileSystem'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Error.Parameters.FileSystem.stream'],
                    $dic['Yapeal.Error.Parameters.FileSystem.level'],
                    $dic['Yapeal.Error.Parameters.FileSystem.bubble'],
                    $dic['Yapeal.Error.Parameters.FileSystem.filePermission'],
                    $dic['Yapeal.Error.Parameters.FileSystem.useLocking']
                ];
                /**
                 * @var \Yapeal\Log\StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Error.Classes.stream'](...$parameters);
                $stream->setPreserve($dic['Yapeal.Error.Parameters.FileSystem.preserve']);
                $lineFormatter = $dic['Yapeal.Error.Parameters.FileSystem.lineFormatter'];
                $stream->setFormatter($dic[$lineFormatter]);
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
    private function wireFingersCrossed(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Error.Callable.FingersCrossed'])) {
            $dic['Yapeal.Error.Callable.FingersCrossed'] = function () use ($dic) {
                /**
                 * @var string $activationStrategy
                 * @var string $handler
                 * @var array  $parameters
                 */
                $activationStrategy = $dic['Yapeal.Error.Parameters.FingersCrossed.activationStrategy'];
                $handler = $dic['Yapeal.Error.Parameters.FingersCrossed.handler'];
                $parameters = [
                    $dic[$handler],
                    $dic[$activationStrategy],
                    $dic['Yapeal.Error.Parameters.FingersCrossed.bufferSize'],
                    $dic['Yapeal.Error.Parameters.FingersCrossed.bubble'],
                    $dic['Yapeal.Error.Parameters.FingersCrossed.stopBuffering'],
                    $dic['Yapeal.Error.Parameters.FingersCrossed.passThruLevel'],
                ];
                return new $dic['Yapeal.Error.Classes.fingersCrossed'](...$parameters);
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
        if (empty($dic['Yapeal.Error.Callable.Group'])) {
            $dic['Yapeal.Error.Callable.Group'] = function () use ($dic) {
                $handlers = [];
                foreach (explode(',', $dic['Yapeal.Error.Parameters.Group.handlers']) as $handler) {
                    if ('' === $handler) {
                        continue;
                    }
                    $handlers[] = $dic[$handler];
                }
                $parameters = [
                    $handlers,
                    $bubble = $dic['Yapeal.Error.Parameters.Group.bubble']
                ];
                return new $dic['Yapeal.Error.Classes.group'](...$parameters);
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
        if (empty($dic['Yapeal.Error.Callable.LineFormatter'])) {
            $dic['Yapeal.Error.Callable.LineFormatter'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Error.Parameters.LineFormatter.format'],
                    $dic['Yapeal.Error.Parameters.LineFormatter.dateFormat'],
                    $dic['Yapeal.Error.Parameters.LineFormatter.allowInlineLineBreaks'],
                    $dic['Yapeal.Error.Parameters.LineFormatter.ignoreEmptyContextAndExtra']
                ];
                /**
                 * @var \Yapeal\Log\LineFormatter $lineFormatter
                 */
                $lineFormatter = new $dic['Yapeal.Error.Classes.lineFormatter'](...$parameters);
                $lineFormatter->includeStacktraces($dic['Yapeal.Error.Parameters.LineFormatter.includeStackTraces']);
                $lineFormatter->setPrettyJson($dic['Yapeal.Error.Parameters.LineFormatter.prettyJson']);
                return $lineFormatter;
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     */
    private function wireLogger(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Error.Callable.Logger'])) {
            $dic['Yapeal.Error.Callable.Logger'] = function () use ($dic) {
                  /**
                 * @var \Yapeal\Log\Logger $logger
                 */
                $handlers = [];
                foreach (explode(',', $dic['Yapeal.Error.Parameters.Logger.handlers']) as $handler) {
                    if ('' === $handler) {
                        continue;
                    }
                    $handlers[] = $dic[$handler];
                }
                $processors = [];
                foreach (explode(',', $dic['Yapeal.Error.Parameters.Logger.processors']) as $processor) {
                    if ('' === $processor) {
                        continue;
                    }
                    $processors[] = $dic[$processor];
                }
                $parameters = [
                    $dic['Yapeal.Error.Parameters.Logger.name'],
                    $handlers,
                    $processors
                ];
                $logger = new $dic['Yapeal.Error.Classes.logger'](...$parameters);
                /**
                 * @var \Monolog\ErrorHandler $error
                 */
                $error = $dic['Yapeal.Error.Classes.error'];
                $error::register($logger,
                    [],
                    $dic['Yapeal.Error.Parameters.Error.exceptionLevel'],
                    $dic['Yapeal.Error.Parameters.Error.fatalLevel']);
                return $logger;
            };
            // Activate error logger now since it is needed to log any future fatal errors or exceptions.
            $dic['Yapeal.Error.Callable.Logger'];
        }
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireStrategy(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Error.Callable.Strategy'])) {
            $dic['Yapeal.Error.Callable.Strategy'] = function () use ($dic) {
                return new $dic['Yapeal.Error.Classes.strategy']((int)$dic['Yapeal.Error.Parameters.Strategy.actionLevel']);
            };
        }
        return $this;
    }
}
