<?php
/**
 * Contains class NetworkWiring.
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

use Yapeal\Container\ContainerInterface;

/**
 * Class NetworkWiring.
 */
class NetworkWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Network.Client'])) {
            $dic['Yapeal.Network.Client'] = function ($dic) {
                $appComment = $dic['Yapeal.Network.appComment'];
                $appName = $dic['Yapeal.Network.appName'];
                $appVersion = $dic['Yapeal.Network.appVersion'];
                if ('' === $appName) {
                    $appComment = '';
                    $appVersion = '';
                }
                $userAgent = trim(str_replace([
                    '{machineType}',
                    '{osName}',
                    '{osRelease}',
                    '{phpVersion}',
                    '{appComment}',
                    '{appName}',
                    '{appVersion}'
                ],
                    [php_uname('m'), php_uname('s'), php_uname('r'), PHP_VERSION, $appComment, $appName, $appVersion],
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
                return new $dic['Yapeal.Network.Handlers.client']($defaults);
            };
        }
        if (empty($dic['Yapeal.Network.Retriever'])) {
            $dic['Yapeal.Network.Retriever'] = function ($dic) {
                return new $dic['Yapeal.Network.Handlers.retrieve']($dic['Yapeal.Network.Client'],
                    $dic['Yapeal.Network.Cache:retrieve']);
            };
        }
        if (!isset($dic['Yapeal.Event.Mediator'])) {
            $mess = 'Tried to call Mediator before it has been added';
            throw new \LogicException($mess);
        }
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $mediator->addServiceSubscriberByEventList('Yapeal.Network.Retriever',
            ['Yapeal.EveApi.retrieve' => ['retrieveEveApi', 'last']]);
        return $this;
    }
}
