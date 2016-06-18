<?php
/**
 * Contains class ErrorWiring.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Yapeal\Container\ContainerInterface;
use Yapeal\Log\Logger;

/**
 * Class ErrorWiring.
 */
class ErrorWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    public function wire(ContainerInterface $dic)
    {
        if (!empty($dic['Yapeal.Error.Logger'])) {
            return $this;
        }
        $dic['Yapeal.Error.Logger'] = function ($dic) {
            /**
             * @var Logger $logger
             */
            $logger = new $dic['Yapeal.Error.class']($dic['Yapeal.Error.channel']);
            $group = [];
            $lineFormatter = new LineFormatter();
            $lineFormatter->includeStacktraces();
            $lineFormatter->allowInlineLineBreaks();
            /**
             * @var \Monolog\Handler\StreamHandler $handler
             */
            if ('cli' === PHP_SAPI) {
                $handler = new $dic['Yapeal.Error.Handlers.stream']('php://stderr', 100);
                $group[] = $handler->setFormatter($lineFormatter);
            }
            $handler = new $dic['Yapeal.Error.Handlers.stream']($dic['Yapeal.Error.dir'] . $dic['Yapeal.Error.fileName'],
                100);
            $group[] = $handler->setFormatter($lineFormatter);
            $logger->pushHandler(
                new $dic['Yapeal.Error.Handlers.fingersCrossed'](new $dic['Yapeal.Error.Handlers.group']($group),
                    (int)$dic['Yapeal.Error.threshold'], (int)$dic['Yapeal.Error.bufferSize'], true, false)
            );
            /**
             * @var ErrorHandler $error
             */
            $error = $dic['Yapeal.Error.Handlers.error'];
            $error::register($logger, [], (int)$dic['Yapeal.Error.threshold'], (int)$dic['Yapeal.Error.threshold']);
            return $error;
        };
        // Activate error logger now since it is needed to log any future fatal
        // errors or exceptions.
        $dic['Yapeal.Error.Logger'];
        return $this;
    }
}
