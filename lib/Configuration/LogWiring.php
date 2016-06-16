<?php
/**
 * Contains class LogWiring.
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

use Monolog\Formatter\LineFormatter;
use Yapeal\Container\ContainerInterface;

/**
 * Class LogWiring.
 */
class LogWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Log.Logger'])) {
            $dic['Yapeal.Log.Logger'] = function () use ($dic) {
                $group = [];
                $lineFormatter = new LineFormatter();
                $lineFormatter->includeStacktraces();
                $lineFormatter->allowInlineLineBreaks();
                /**
                 * @var \Monolog\Handler\StreamHandler $handler
                 */
                if (PHP_SAPI === 'cli') {
                    $handler = new $dic['Yapeal.Log.Handlers.stream'](
                        'php://stderr', 100
                    );
                    $handler->setFormatter($lineFormatter);
                    $group[] = $handler;
                }
                $handler = new $dic['Yapeal.Log.Handlers.stream'](
                    $dic['Yapeal.Log.dir'] . $dic['Yapeal.Log.fileName'], 100
                );
                $handler->setFormatter($lineFormatter);
                $group[] = $handler;
                return new $dic['Yapeal.Log.class'](
                    $dic['Yapeal.Log.channel'], [
                        new $dic['Yapeal.Log.Handlers.fingersCrossed'](
                            new $dic['Yapeal.Log.Handlers.group']($group),
                            (int)$dic['Yapeal.Log.threshold'],
                            (int)$dic['Yapeal.Log.bufferSize']
                        )
                    ]
                );
            };
        }
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $mediator->addServiceSubscriberByEventList(
            'Yapeal.Log.Logger',
            ['Yapeal.Log.log' => ['logEvent', 'last']]
        );
        return $this;
    }
}
