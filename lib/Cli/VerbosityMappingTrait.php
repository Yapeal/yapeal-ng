<?php
declare(strict_types = 1);
/**
 * Contains trait VerbosityMappingTrait.
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
namespace Yapeal\Cli;

use Symfony\Component\Console\Output\OutputInterface;
use Yapeal\Log\Logger;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/**
 * Trait VerbosityMappingTrait.
 *
 * @method \Yapeal\Container\ContainerInterface getDic()
 */
trait VerbosityMappingTrait
{
    /**
     * @param OutputInterface $output
     *
     * @return $this Fluent Interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    protected function applyVerbosityMap(OutputInterface $output)
    {
        $dic = $this->getDic();
        $verbosity = $output->getVerbosity();
        /**
         * @var \Yapeal\Event\MediatorInterface $yem
         * @var \Yapeal\Log\ActivationStrategy  $strategy
         * @var \Yapeal\Log\LineFormatter       $lineFormatter
         * @var \Yapeal\Log\StreamHandler       $cliStream
         * @var \Yapeal\Log\StreamHandler       $fileSystemStream
         */
        $yem = $dic['Yapeal.Event.Mediator'];
        $cliStream = $dic['Yapeal.Log.Callable.Cli'];
        $fileSystemStream = $dic['Yapeal.Log.Callable.FileSystem'];
        $lineFormatter = $dic['Yapeal.Log.Callable.LineFormatter'];
        $strategy = $dic['Yapeal.Log.Callable.Strategy'];
        $cliStream->setPreserve(true);
        $fileSystemStream->setPreserve(true);
        $lineFormatter->setPrettyJson(true);
        switch ($verbosity) {
            case $output::VERBOSITY_QUIET:
                $cliStream->setPreserve(false);
                $lineFormatter->setPrettyJson(false);
                $strategy->setActionLevel(Logger::ERROR);
                $mess = 'Yapeal-ng has switched to silent running mode where beyond this point only ERROR or higher level messages will trigger logging';
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $mess);
                break;
            case $output::VERBOSITY_NORMAL:
                $cliStream->setPreserve(false);
                $lineFormatter->setPrettyJson(false);
                $strategy->setActionLevel(Logger::WARNING);
                $mess = 'Yapeal-ng has switched to normal mode where beyond this point WARNING or higher level messages will trigger logging';
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
                break;
            case $output::VERBOSITY_VERBOSE:
                $cliStream->setPreserve(true);
                $strategy->setActionLevel(Logger::NOTICE);
                $mess = 'Yapeal-ng has switched to verbose mode where beyond this point any NOTICE or higher level messages will trigger logging';
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                break;
            case $output::VERBOSITY_VERY_VERBOSE:
                $cliStream->setPreserve(true);
                $strategy->setActionLevel(Logger::INFO);
                $mess = 'Yapeal-ng switched to very verbose mode where beyond this point any INFO or higher level messages will trigger logging';
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
                break;
            case $output::VERBOSITY_DEBUG:
                $cliStream->setPreserve(true);
                $strategy->setActionLevel(Logger::DEBUG);
                $mess = 'Yapeal-ng has switched to debug mode where beyond this point any kind of message will trigger logging';
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
                break;
            default:
                $mess = 'Unknown verbosity setting received, Aborting ...';
                throw new \RuntimeException($mess, 2);
        }
        return $this;
    }
}
