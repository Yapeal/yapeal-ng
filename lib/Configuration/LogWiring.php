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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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
            ->wireCli($dic)
            ->wireFileSystem($dic)
            ->wireGroup($dic)
            ->wireStrategy($dic)
            ->wireFingersCrossed($dic)
            ->wireLogger($dic)
            ->registerErrorHandler($dic)
            ->registerExceptionHandler($dic)
            ->registerFatalHandler($dic);
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function registerErrorHandler(ContainerInterface $dic): self
    {
        if ($dic['Yapeal.Log.Parameters.Register.errorHandler'] && empty($dic['Yapeal.Log.Callable.ErrorHandler'])) {
            $dic['Yapeal.Log.Callable.ErrorHandler'] = function (ContainerInterface $dic) {
                $parameters = [
                    $dic['Yapeal.Log.Callable.Logger'],
                    (array)json_decode($dic['Yapeal.Log.Parameters.Register.errorLevelMap'])
                ];
                /** @noinspection PhpTooManyParametersInspection */
                $errorHandler = new class(...$parameters)
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
                     * This is what PHP will actual call to handle any errors.
                     *
                     * Note that PHP's 'ini' error mask is ignored so the log files have complete info. This will
                     * also cause them to be seen on the command line where they might not have been before.
                     *
                     * This method acts in a sufficiently handled manner. So if there was a previous error handler it is
                     * given a chance to process any errors as well and then this method returns true if either itself
                     * or the previous handler returns true otherwise it'll return false so PHP can decide what to do
                     * next. This should let any testing frameworks to work without interference from Yapeal-ng.
                     *
                     * @param int    $code
                     * @param string $message
                     * @param string $file
                     * @param int    $line
                     * @param array  $context
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
                        $handled = false;
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
                            $handled = true;
                        }
                        if (null !== $this->previousErrorHandler) {
                            return $handled
                                || (bool)call_user_func($this->previousErrorHandler,
                                    $code,
                                    $message,
                                    $file,
                                    $line,
                                    $context);
                        }
                        return $handled;
                    }
                    /**
                     * @var callable|null $previousErrorHandler
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
                $prev = set_error_handler($errorHandler);
                $errorHandler->previousErrorHandler = $prev;
            };
            $dic['Yapeal.Log.Callable.ErrorHandler'];
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
        if ($dic['Yapeal.Log.Parameters.Register.exceptionHandler']
            && empty($dic['Yapeal.Log.Callable.ExceptionHandler'])) {
            $dic['Yapeal.Log.Callable.ExceptionHandler'] = function (ContainerInterface $dic) {
                $parameters = [
                    $dic['Yapeal.Log.Callable.Logger'],
                    $dic['Yapeal.Log.Parameters.Register.exceptionLevel']
                ];
                $exceptionHandler = new class(...$parameters) {
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
                     * This is actually what PHP will call for uncaught error or regular exception.
                     *
                     * This method acts in a sufficiently handled manner so if there was a previous handler it'll get a
                     * chance to see the exception as well. This should let any testing frameworks to work without
                     * interference from Yapeal-ng.
                     *
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
                        exit(254);
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
            };
            $dic['Yapeal.Log.Callable.ExceptionHandler'];
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function registerFatalHandler(ContainerInterface $dic): self
    {
        if ($dic['Yapeal.Log.Parameters.Register.fatalHandler'] && empty($dic['Yapeal.Log.Callable.FatalHandler'])) {
            $dic['Yapeal.Log.Callable.FatalHandler'] = function (ContainerInterface $dic) {
                $parameters = [
                    $dic['Yapeal.Log.Callable.Logger'],
                    $dic['Yapeal.Log.Parameters.Register.fatalLevel'],
                    $dic['Yapeal.Log.Parameters.Register.fatalReservedMemorySize']
                ];
                $fatalHandler = new class(...$parameters) {
                    /**
                     *  constructor.
                     *
                     * @param LoggerInterface $logger
                     * @param int             $level
                     * @param int             $reservedMemorySize
                     */
                    public function __construct(
                        LoggerInterface $logger,
                        int $level = null,
                        int $reservedMemorySize = 20
                    ) {
                        $this->logger = $logger;
                        $this->fatalLevel = $level;
                        $this->reservedMemory = str_repeat(' ', 1024 * $reservedMemorySize);
                    }
                    /**
                     * This is the actual method PHP will call for any fatal errors.
                     */
                    public function __invoke()
                    {
                        // Frees up the reserved memory so there's some available to do our work in since one cause of a
                        // fatal error is running out of memory and cause another out of memory error here would prevent
                        // it being logged.
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
            };
            $dic['Yapeal.Log.Callable.FatalHandler'];
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
            /**
             * @param ContainerInterface $dic
             *
             * @return HandlerInterface
             */
            $dic['Yapeal.Log.Callable.Cli'] = function (ContainerInterface $dic): HandlerInterface {
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
            /**
             * @param ContainerInterface $dic
             *
             * @return HandlerInterface
             */
            $dic['Yapeal.Log.Callable.FileSystem'] = function (ContainerInterface $dic): HandlerInterface {
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
            /**
             * @param ContainerInterface $dic
             *
             * @return HandlerInterface
             */
            $dic['Yapeal.Log.Callable.FingersCrossed'] = function (ContainerInterface $dic): HandlerInterface {
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
                    $dic['Yapeal.Log.Parameters.FingersCrossed.passThruLevel']
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
            /**
             * @param ContainerInterface $dic
             *
             * @return HandlerInterface
             */
            $dic['Yapeal.Log.Callable.Group'] = function (ContainerInterface $dic): HandlerInterface {
                $handlerList = $dic['Yapeal.daemon'] ? $dic['Yapeal.Log.Parameters.Group.daemonHandlers'] : $dic['Yapeal.Log.Parameters.Group.handlers'];
                $handlers = [];
                foreach (explode(',', $handlerList) as $handler) {
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
            $dic['Yapeal.Log.Callable.LFFactory'] = $dic->factory(/**
             * @param ContainerInterface $dic
             *
             * @return FormatterInterface
             */
                function (ContainerInterface $dic): FormatterInterface {
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
    private function wireLogger(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Log.Callable.Logger'])) {
            /**
             * @param ContainerInterface $dic
             *
             * @return Logger
             */
            $dic['Yapeal.Log.Callable.Logger'] = function (ContainerInterface $dic): Logger {
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
            /**
             * @var \Yapeal\Event\MediatorInterface $mediator
             */
            $mediator = $dic['Yapeal.Event.Callable.Mediator'];
            $mediator->addServiceListener('Yapeal.Log.log', ['Yapeal.Log.Callable.Logger', 'logEvent'], 'last');
            $mediator->addServiceListener('Yapeal.Log.error', ['Yapeal.Log.Callable.Logger', 'logEvent'], 'last');
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
            /**
             * @param ContainerInterface $dic
             *
             * @return ActivationStrategyInterface
             */
            $dic['Yapeal.Log.Callable.Strategy'] = function (ContainerInterface $dic): ActivationStrategyInterface {
                return new $dic['Yapeal.Log.Classes.strategy']((int)$dic['Yapeal.Log.Parameters.Strategy.actionLevel']);
            };
        }
        return $this;
    }
}
