<?php
declare(strict_types = 1);
/**
 * Contains CacheRetriever class.
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
namespace Yapeal\FileSystem;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EveApiRetrieverInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class CacheRetriever
 */
class CacheRetriever implements EveApiRetrieverInterface
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait;
    /**
     * @param string|null $cachePath
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($cachePath = null)
    {
        $this->setCachePath($cachePath);
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \LogicException
     */
    public function retrieveEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        if (!$this->shouldRetrieve()) {
            return $event;
        }
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        // BaseSection/ApiHash.xml
        $cacheFile = sprintf('%1$s%2$s/%3$s%4$s.xml',
            $this->getCachePath(),
            ucfirst($data->getEveApiSectionName()),
            ucfirst($data->getEveApiName()),
            $data->getHash());
        $result = $this->safeFileRead($cacheFile);
        if (false === $result) {
            return $event;
        }
        $data->setEveApiXml($result);
        if ($this->isExpired($data)) {
            $this->deleteWithRetry($cacheFile);
            return $event;
        }
        $mess = sprintf('Found usable cache file %1$s', $cacheFile);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEventMessage($mess, $data, $eventName));
        return $event->setData($data)
            ->eventHandled();
    }
    /**
     * @param string|null $value
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setCachePath($value = null)
    {
        if ($value === null) {
            $value = dirname(dirname(__DIR__)) . '/cache/';
        }
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string, but was given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->cachePath = $this->getFpn()
            ->normalizePath($value);
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
    public function setRetrieve(bool $value = true)
    {
        $this->retrieve = (boolean)$value;
        return $this;
    }
    /**
     * @return string
     * @throws \LogicException
     */
    protected function getCachePath()
    {
        if ('' === $this->cachePath) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new \LogicException($mess);
        }
        return $this->getFpn()
            ->normalizePath($this->cachePath);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws \DomainException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function isExpired(EveApiReadWriteInterface $data)
    {
        $xml = $data->getEveApiXml();
        $simple = new \SimpleXMLElement($xml);
        /** @noinspection PhpUndefinedFieldInspection */
        $current = $simple->currentTime[0];
        /** @noinspection PhpUndefinedFieldInspection */
        $until = $simple->cachedUntil[0];
        if (null === $current) {
            $mess = 'Cached XML file missing required currentTime element in';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $this->createEveApiMessage($mess, $data));
            return true;
        }
        if (null === $until) {
            $mess = 'Cached XML file missing required cachedUntil element in';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $this->createEveApiMessage($mess, $data));
            return true;
        }
        $now = time();
        $current = strtotime($current . '+00:00');
        $until = strtotime($until . '+00:00');
        // At minimum use cached XML for 5 minutes (300 secs).
        if (($now - $current) <= 300) {
            return false;
        }
        // Catch and log APIs with bad CachedUntil times so CCP can be told and get them fixed.
        if ($until <= $current) {
            $mess = sprintf('CachedUntil is invalid was given %1$s and currentTime is %2$s in', $until, $current);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($mess, $data));
            return true;
        }
        // Now plus a day.
        if ($until > ($now + 86400)) {
            $mess = sprintf('CachedUntil is excessively long was given %1$s and currentTime is %2$s', $until, $current);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return true;
        }
        return ($until <= $now);
    }
    /**
     * @var string $cachePath
     */
    protected $cachePath;
    /**
     * @return boolean
     */
    private function shouldRetrieve()
    {
        return $this->retrieve;
    }
    /**
     * @var bool $retrieve
     */
    private $retrieve = false;
}
