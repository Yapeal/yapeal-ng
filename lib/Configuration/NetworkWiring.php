<?php
declare(strict_types = 1);
/**
 * Contains class NetworkWiring.
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
 * Class NetworkWiring.
 */
class NetworkWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @throws \LogicException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Network.Callable.Client'])) {
            $dic['Yapeal.Network.Callable.Client'] = function ($dic) {
                $userAgent = trim(str_replace([
                    '{machineType}',
                    '{osName}',
                    '{osRelease}',
                    '{phpVersion}'
                ],
                    [php_uname('m'), php_uname('s'), php_uname('r'), PHP_VERSION],
                    $dic['Yapeal.Network.userAgent']));
                $userAgent = ltrim($userAgent, '/ ');
                $headers = [
                    'Accept' => $dic['Yapeal.Network.Headers.Accept'],
                    'Accept-Charset' => $dic['Yapeal.Network.Headers.Accept-Charset'],
                    'Accept-Encoding' => $dic['Yapeal.Network.Headers.Accept-Encoding'],
                    'Accept-Language' => $dic['Yapeal.Network.Headers.Accept-Language'],
                    'Connection' => $dic['Yapeal.Network.Headers.Connection'],
                    'Keep-Alive' => $dic['Yapeal.Network.Headers.Keep-Alive']
                ];
                // Clean up any extra spaces and EOL chars from Yaml.
                array_walk($headers,
                    function (&$value) {
                        $value = trim(str_replace(' ', '', (string)$value));
                    });
                if ('' !== $userAgent) {
                    $headers['User-Agent'] = $userAgent;
                }
                $defaults = [
                    'base_uri' => $dic['Yapeal.Network.baseUrl'],
                    'connect_timeout' => (int)$dic['Yapeal.Network.connect_timeout'],
                    'headers' => $headers,
                    'timeout' => (int)$dic['Yapeal.Network.timeout'],
                    'verify' => $dic['Yapeal.Network.verify']
                ];
                return new $dic['Yapeal.Network.Classes.client']($defaults);
            };
        }
        if (empty($dic['Yapeal.Network.Callable.Retriever'])) {
            $dic['Yapeal.Network.Callable.Retriever'] = function ($dic) {
                return new $dic['Yapeal.Network.Classes.retrieve']($dic['Yapeal.Network.Callable.Client'],
                    (bool)$dic['Yapeal.Network.Cache.retrieve']);
            };
        }
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Callable.Mediator'];
        $mediator->addServiceListener('Yapeal.EveApi.retrieve',
            ['Yapeal.Network.Callable.Retriever', 'retrieveEveApi'],
            'last');
        $mediator->addServiceListener('Yapeal.EveApi.Raw.retrieve',
            ['Yapeal.Network.Callable.Retriever', 'retrieveEveApi'],
            'last');
    }
}
