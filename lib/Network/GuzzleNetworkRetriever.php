<?php
declare(strict_types=1);
/**
 * Contains GuzzleNetworkRetriever class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Network;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EveApiRetrieverInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class GuzzleNetworkRetriever
 *
 * @author Stephen Gulick <stephenmg12@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
class GuzzleNetworkRetriever implements EveApiRetrieverInterface
{
    use EveApiEventEmitterTrait;
    /**
     * @param Client $client
     * @param bool   $preserve
     */
    public function __construct(Client $client, $preserve = true)
    {
        $this->setClient($client)
            ->setRetrieve($preserve);
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return \Yapeal\Event\EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function retrieveEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        if (!$this->shouldRetrieve()) {
            return $event;
        }
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        $uri = sprintf('/%1$s/%2$s.xml.aspx', strtolower($data->getEveApiSectionName()), $data->getEveApiName());
        try {
            $response = $this->getClient()
                ->post($uri, ['form_params' => $data->getEveApiArguments()]);
        } catch (ClientException $exc) {
            if ($exc->hasResponse()) {
                $response = $exc->getResponse();
            } else {
                $messagePrefix = 'Could NOT retrieve XML data during';
                $yem->triggerLogEvent('Yapeal.Log.log',
                    Logger::DEBUG,
                    $this->createEventMessage($messagePrefix, $data, $eventName),
                    ['exception' => $exc]);
                return $event;
            }
        } catch (RequestException $exc) {
            $messagePrefix = 'Could NOT retrieve XML data during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::DEBUG,
                $this->createEventMessage($messagePrefix, $data, $eventName),
                ['exception' => $exc]);
            return $event;
        }
        $body = (string)$response->getBody();
        if ('' === $body) {
            $messagePrefix = 'Received empty body during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::NOTICE,
                $this->createEventMessage($messagePrefix, $data, $eventName));
        }
        $data->setEveApiXml($body);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getFinishedEventMessage($data, $eventName));
        return $event->setData($data)
            ->setHandledSufficiently();
    }
    /**
     * @param Client $value
     *
     * @return $this Fluent interface.
     */
    public function setClient(Client $value)
    {
        $this->client = $value;
        return $this;
    }
    /**
     * Turn on or off retrieving of Eve API data by this retriever.
     *
     * Allows class to stay registered for events but be enabled or disabled during runtime.
     *
     * @param boolean $value
     *
     * @return $this Fluent interface
     */
    public function setRetrieve($value = true)
    {
        $this->retrieve = (boolean)$value;
        return $this;
    }
    /**
     * @return Client
     * @throws \LogicException
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $mess = 'Tried to use client before it was set';
            throw new \LogicException($mess);
        }
        return $this->client;
    }
    /**
     * @return boolean
     */
    private function shouldRetrieve()
    {
        return $this->retrieve;
    }
    /**
     * @var Client $client
     */
    private $client;
    /**
     * @var bool $retrieve
     */
    private $retrieve = true;
}
