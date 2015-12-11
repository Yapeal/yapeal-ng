<?php
/**
 * Contains GuzzleNetworkRetriever class.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Network;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use LogicException;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Log\MessageBuilderTrait;

/**
 * Class GuzzleNetworkRetriever
 *
 * @author Stephen Gulick <stephenmg12@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
class GuzzleNetworkRetriever
{
    use EveApiEventEmitterTrait, MessageBuilderTrait;
    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->setClient($client);
    }
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function retrieveEveApi(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
    {
        $data = $event->getData();
        $this->getYem()
             ->triggerLogEvent(
                 'Yapeal.Log.log',
                 Logger::DEBUG,
                 $this->getReceivedEventMessage($data, $eventName, __CLASS__)
             );
        $uri = sprintf('/%1$s/%2$s.xml.aspx', strtolower($data->getEveApiSectionName()), $data->getEveApiName());
        try {
            $response = $this->getClient()
                             ->post($uri, ['form_params' => $data->getEveApiArguments()]);
        } catch (RequestException $exc) {
            $messagePrefix = 'Could NOT retrieve XML data during:';
            $yem->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->createEventMessage($messagePrefix, $data, $eventName),
                ['exception' => $exc]
            );
            return $event;
        }
        $body = (string)$response->getBody();
        if ('' === $body) {
            $messagePrefix = 'Received empty body during:';
            $yem->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::NOTICE,
                $this->createEventMessage($messagePrefix, $data, $eventName)
            );
        }
        $data->setEveApiXml($body);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getFinishedEventMessage($data, $eventName));
        return $event->setData($data)
                     ->eventHandled();
    }
    /**
     * @param Client|null $value
     *
     * @return self Fluent interface.
     */
    public function setClient(Client $value = null)
    {
        $this->client = $value;
        return $this;
    }
    /**
     * @return Client
     * @throws LogicException
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $mess = 'Tried to use client before it was set';
            throw new LogicException($mess);
        }
        return $this->client;
    }
    /**
     * @type Client $client
     */
    protected $client;
}
