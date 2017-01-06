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
        $this->wireMergedParametersCallable($dic)
            ->wireClient($dic)
            ->wireRetriever($dic);
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface
     */
    private function wireClient(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Network.Callable.Client'])) {
            $dic['Yapeal.Network.Callable.Client'] = function (ContainerInterface $dic) {
                /**
                 * @var array $clientParameters
                 */
                $clientParameters = $dic['Yapeal.Network.Callable.GetClientMergedParameters'];
                $headers = [
                    'Accept' => $clientParameters['accept'],
                    'Accept-Charset' => $clientParameters['acceptCharset'],
                    'Accept-Encoding' => $clientParameters['acceptEncoding'],
                    'Accept-Language' => $clientParameters['acceptLanguage'],
                    'Connection' => $clientParameters['connection'],
                    'Keep-Alive' => $clientParameters['keepAlive']
                ];
                // Clean up any extra spaces and EOL chars from Yaml.
                array_walk($headers,
                    function (&$value) {
                        $value = trim(str_replace(' ', '', (string)$value));
                    });
                $agentSubs = [
                    '{appComment}' => $clientParameters['appComment'],
                    '{appName}' => $clientParameters['appName'],
                    '{appVersion}' => $clientParameters['appVersion'],
                    '{machineType}' => php_uname('m'),
                    '{osName}' => php_uname('s'),
                    '{osRelease}' => php_uname('r'),
                    '{phpVersion}' => PHP_VERSION
                ];
                $userAgent = str_replace(array_keys($agentSubs),
                    array_values($agentSubs),
                    $dic['Yapeal.Network.userAgent']);
                $userAgent = ltrim(trim($userAgent), '/ ');
                if ('' !== $userAgent) {
                    $headers['User-Agent'] = $userAgent;
                }
                $defaults = [
                    'base_uri' => $clientParameters['baseUrl'],
                    'connect_timeout' => $clientParameters['connect_timeout'],
                    'headers' => $headers,
                    'timeout' => $clientParameters['timeout'],
                    'verify' => $clientParameters['verify']
                ];
                return new $dic['Yapeal.Network.Classes.client']($defaults);
            };
        }
        return $this;
    }
    /**
     * Used to get an extracted and merged set of client parameters.
     *
     * Note that normal Yapeal-ng config file substitutions will have already been applied before this callable sees
     * the parameters so things like ```{Yapeal.Network.appComment}``` will have already been replaced.
     *
     * This extract all scalars parameters with prefixes of:
     * Yapeal.Network.Parameters.
     * Yapeal.Network.Parameters.client.
     * Yapeal.Network.Parameters.client.$server.
     * Yapeal.Network.Parameters.client.$server.headers.
     *
     * where $server is the value of Yapeal.Network.server parameter. Overlapping parameters from the later prefixes
     * will overwrite values from earlier prefixes.
     *
     * __NOTE:__
     *     ```Yapeal.Network.Parameters.client.server``` is treated differently in that the matching parameter
     *     ```Yapeal.Network.server``` will _not_ be used. This make sense if you think about it as its only the client
     *     and nothing else that needs to known which Eve API server is being used.
     *
     *
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireMergedParametersCallable(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Network.Callable.GetClientMergedParameters'])) {
            $dic['Yapeal.Network.Callable.GetClientMergedParameters'] = function (ContainerInterface $dic) {
                $getScalars = $dic['Yapeal.Config.Callable.ExtractScalarsByKeyPrefix'];
                $base = [];
                foreach ($getScalars($dic, 'Yapeal.Network.') as $index => $item) {
                    $base[$index] = $item;
                }
                $clientBase = [];
                foreach ($getScalars($dic, 'Yapeal.Network.Parameters.client.') as $index => $item) {
                    $clientBase[$index] = $item;
                }
                if (!array_key_exists('server', $clientBase)) {
                    $mess = '"Yapeal.Network.Parameters.client.server" parameter must exist in at least one config file'
                        . ' that is added to the Container';
                    throw new \OutOfBoundsException($mess);
                }
                $server = $clientBase['server'];
                $perServer = [];
                // Per server parameters.
                $serverParameters = sprintf('Yapeal.Network.Parameters.client.%s.', $server);
                foreach ($getScalars($dic, $serverParameters) as $index => $item) {
                    $perServer[$index] = $item;
                }
                $perHeader = [];
                // Per server headers parameters.
                $headerParameters = sprintf('Yapeal.Network.Parameters.client.%s.headers', $server);
                foreach ($getScalars($dic, $headerParameters) as $index => $item) {
                    $perHeader[$index] = $item;
                }
                return array_merge($base, $clientBase, $perServer, $perHeader);
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     */
    private function wireRetriever(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Network.Callable.Retriever'])) {
            $dic['Yapeal.Network.Callable.Retriever'] = function (ContainerInterface $dic) {
                return new $dic['Yapeal.Network.Classes.retrieve']($dic['Yapeal.Network.Callable.Client'],
                    $dic['Yapeal.Network.Parameters.retrieve']);
            };
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
}
