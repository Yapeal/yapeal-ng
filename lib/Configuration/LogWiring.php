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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yapeal\Container\ContainerInterface;

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
            ->wireCli($dic)
            ->wireFileSystem($dic)
            ->wireGroup($dic)
            ->wireStrategy($dic)
            ->wireFingersCrossed($dic)
            ->wireLogger($dic)
            ->registerErrorHandler($dic)
            ->registerExceptionHandler($dic)
            ->registerFatalHandler($dic);
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Callable.Mediator'];
        $mediator->addServiceListener('Yapeal.Log.log', ['Yapeal.Log.Callable.Logger', 'logEvent'], 'last');
        $mediator->addServiceListener('Yapeal.Log.error', ['Yapeal.Log.Callable.Logger', 'logEvent'], 'last');
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function registerErrorHandler(ContainerInterface $dic): self
    {
        $errorLevelMap = $dic['Yapeal.Log.Parameters.Register.errorLevelMap'];
        if (false !== $errorLevelMap) {
            $errorLevelMap = (array)json_decode($errorLevelMap);
            /** @noinspection PhpTooManyParametersInspection */
            $errorHandler = new class($dic['Yapeal.Log.Callable.Logger'], $errorLevelMap)
            {
                /**
                 *  constructor.
                 *
                 * @param LoggerInterface $logger
                 * @param array           $errorLevelMap
                 */
                public function __construct(LoggerInterface $logger, array $errorLevelMap = [])
                {
                    $this->logger = $logger;
                    $this->errorLevelMap = $errorLevelMap;
                }
                /**
                 * @param int $code
                 * @param string $message
                 * @param string $file
                 * @param int $line
                 * @param array $context
                 *
                 * @return bool
                 */
                public function __invoke(
                    int $code,
                    string $message,
                    string $file = '',
                    int $line = 0,
                    array $context = []
                ): bool {
                    if (!(error_reporting() & $code)) {
                        return false;
                    }
                    if ($code & self::HANDLE_ERRORS) {
                        $level = $this->errorLevelMap[$code] ?? LogLevel::CRITICAL;
                        $this->logger->log($level,
                            $message,
                            [
                                'code' => $code,
                                'message' => $message,
                                'file' => str_replace('\\', '/', $file),
                                'line' => $line
                            ]);
                    }
                    if ($this->previousErrorHandler === true) {
                        return false;
                    } elseif ($this->previousErrorHandler) {
                        return (bool)call_user_func($this->previousErrorHandler,
                            $code,
                            $message,
                            $file,
                            $line,
                            $context);
                    }
                    return true;
                }
                /**
                 * @var callable|true $previousErrorHandler
                 */
                public $previousErrorHandler;
                /**
                 * @var array $errorLevelMap
                 */
                private $errorLevelMap;
                /**
                 * @var LoggerInterface $logger
                 */
                private $logger;
                const IGNORED_ERRORS = E_COMPILE_ERROR
                | E_COMPILE_WARNING
                | E_CORE_ERROR
                | E_CORE_WARNING
                | E_ERROR
                | E_PARSE
                | E_USER_ERROR;
                const HANDLE_ERRORS = E_ALL & ~self::IGNORED_ERRORS;
            };
            $prev = set_error_handler($errorHandler, $errorHandler::HANDLE_ERRORS);
            $errorHandler->previousErrorHandler = $prev;
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function registerExceptionHandler(ContainerInterface $dic): self
    {
        $exceptionLevel = $dic['Yapeal.Log.Parameters.Register.exceptionLevel'];
        if (false !== $exceptionLevel) {
            $exceptionHandler = new class($dic['Yapeal.Log.Callable.Logger'], $exceptionLevel)
            {
                /**
                 *  constructor.
                 *
                 * @param LoggerInterface $logger
                 * @param int|null        $uncaughtExceptionLevel
                 */
                public function __construct(LoggerInterface $logger, int $uncaughtExceptionLevel)
                {
                    $this->logger = $logger;
                    $this->uncaughtExceptionLevel = $uncaughtExceptionLevel;
                }
                /**
                 * @param \Throwable $exc
                 */
                public function __invoke(\Throwable $exc)
                {
                    $level = $this->uncaughtExceptionLevel ?? LogLevel::ERROR;
                    $this->logger->log($level,
                        sprintf('Uncaught Exception %s: "%s" at %s line %s',
                            get_class($exc),
                            $exc->getMessage(),
                            str_replace('\\', '/', $exc->getFile()),
                            $exc->getLine()),
                        ['exception' => $exc]);
                    if (null !== $this->previousExceptionHandler) {
                        call_user_func($this->previousExceptionHandler, $exc);
                    }
                    exit(255);
                }
                /**
                 * @var string|null $previousExceptionHandler
                 */
                public $previousExceptionHandler;
                /**
                 * @var LoggerInterface $logger
                 */
                private $logger;
                /**
                 * @var int|null $uncaughtExceptionLevel
                 */
                private $uncaughtExceptionLevel;
            };
            $prev = set_exception_handler($exceptionHandler);
            $exceptionHandler->previousExceptionHandler = $prev;
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function registerFatalHandler(ContainerInterface $dic)
    {
        $fatalLevel = $dic['Yapeal.Log.Parameters.Register.fatalLevel'];
        if (false !== $fatalLevel) {
            $fatalHandler = new class($dic['Yapeal.Log.Callable.Logger'], $fatalLevel)
            {
                /**
                 *  constructor.
                 *
                 * @param LoggerInterface $logger
                 * @param int             $level
                 * @param int             $reservedMemorySize
                 */
                public function __construct(LoggerInterface $logger, int $level = null, int $reservedMemorySize = 20)
                {
                    $this->logger = $logger;
                    $this->fatalLevel = $level;
                    $this->reservedMemory = str_repeat(' ', 1024 * $reservedMemorySize);
                }
                public function __invoke()
                {
                    $this->reservedMemory = null;
                    $lastError = error_get_last();
                    if ($lastError && ($lastError['type'] & self::FATAL_ERRORS)) {
                        $this->logger->log($this->fatalLevel ?? LogLevel::ALERT,
                            $lastError['message'],
                            [
                                'code' => $lastError['type'],
                                'message' => $lastError['message'],
                                'file' => str_replace('\\', '/', $lastError['file']),
                                'line' => $lastError['line']
                            ]);
                        if (method_exists($this->logger, 'getHandlers')) {
                            foreach ($this->logger->getHandlers() as $handler) {
                                if (method_exists($handler, 'close')) {
                                    $handler->close();
                                }
                            }
                        }
                    }
                }
                const FATAL_ERRORS = E_COMPILE_ERROR
                | E_COMPILE_WARNING
                | E_CORE_ERROR
                | E_CORE_WARNING
                | E_ERROR
                | E_PARSE
                | E_USER_ERROR;
                /**
                 * @var int|null $fatalLevel
                 */
                private $fatalLevel;
                /**
                 * @var LoggerInterface $logger
                 */
                private $logger;
                /**
                 * @var string $reservedMemory
                 */
                private $reservedMemory;
            };
            register_shutdown_function($fatalHandler);
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireCli(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.Cli'])) {
            $dic['Yapeal.Log.Callable.Cli'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Log.Parameters.Cli.stream'],
                    $dic['Yapeal.Log.Parameters.Cli.level'],
                    $dic['Yapeal.Log.Parameters.Cli.bubble'],
                    $dic['Yapeal.Log.Parameters.Cli.filePermission'],
                    $dic['Yapeal.Log.Parameters.Cli.useLocking']
                ];
                /**
                 * @var \Yapeal\Log\StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Log.Classes.stream'](...$parameters);
                $stream->setPreserve($dic['Yapeal.Log.Parameters.Cli.preserve']);
                $lineFormatter = $dic['Yapeal.Log.Parameters.Cli.lineFormatter'];
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
        if (empty($dic['Yapeal.Log.Callable.FileSystem'])) {
            $dic['Yapeal.Log.Callable.FileSystem'] = function () use ($dic) {
                $parameters = [
                    $dic['Yapeal.Log.Parameters.FileSystem.stream'],
                    $dic['Yapeal.Log.Parameters.FileSystem.level'],
                    $dic['Yapeal.Log.Parameters.FileSystem.bubble'],
                    $dic['Yapeal.Log.Parameters.FileSystem.filePermission'],
                    $dic['Yapeal.Log.Parameters.FileSystem.useLocking']
                ];
                /**
                 * @var \Yapeal\Log\StreamHandler $stream
                 */
                $stream = new $dic['Yapeal.Log.Classes.stream'](...$parameters);
                $stream->setPreserve($dic['Yapeal.Log.Parameters.FileSystem.preserve']);
                $lineFormatter = $dic['Yapeal.Log.Parameters.FileSystem.lineFormatter'];
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
        if (empty($dic['Yapeal.Log.Callable.FingersCrossed'])) {
            $dic['Yapeal.Log.Callable.FingersCrossed'] = function () use ($dic) {
                /**
                 * @var string $activationStrategy
                 * @var string $handler
                 * @var array  $parameters
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
                return new $dic['Yapeal.Log.Classes.fingersCrossed'](...$parameters);
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
        if (empty($dic['Yapeal.Log.Callable.LFFactory'])) {
            $dic['Yapeal.Log.Callable.LFFactory'] = $dic->factory(function () use ($dic) {
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
                $lineFormatter->setPrettyJson($dic['Yapeal.Log.Parameters.LineFormatter.prettyJson']);
                return $lineFormatter;
            });
        }
        if (empty($dic['Yapeal.Log.Callable.CliLF'])) {
            $dic['Yapeal.Log.Callable.CliLF'] = $dic['Yapeal.Log.Callable.LFFactory'];
        }
        if (empty($dic['Yapeal.Log.Callable.FileSystemLF'])) {
            $dic['Yapeal.Log.Callable.FileSystemLF'] = $dic['Yapeal.Log.Callable.LFFactory'];
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
                 * @var \Yapeal\Log\Logger $logger
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
