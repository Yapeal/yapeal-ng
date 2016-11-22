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
use Yapeal\Xml\LibXmlChecksTrait;

/**
 * Class CacheRetriever
 */
class CacheRetriever implements EveApiRetrieverInterface
{
    use EveApiEventEmitterTrait;
    use LibXmlChecksTrait;
    use SafeFileHandlingTrait;
    /**
     * CacheRetriever constructor.
     *
     * @param string $cachePath
     */
    public function __construct(string $cachePath)
    {
        $this->setCachePath($cachePath);
    }
    /**
     * Method that is called for retrieve event.
     *
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function retrieveEveApi(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface {
        if (!$this->shouldRetrieve()) {
            return $event;
        }
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        if (false === $xml = $this->getXmlFileContents($data)) {
            return $event;
        }
        $data->setEveApiXml($xml);
        $mess = 'Successfully retrieved the XML of';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
        return $event->setData($data)
            ->setHandledSufficiently();
    }
    /**
     * Set cache path for Eve API XML.
     *
     * @param string $value Absolute path to cache/ directory.
     *
     * @return self Fluent interface.
     */
    public function setCachePath(string $value): self
    {
        $this->cachePath = $value;
        return $this;
    }
    /**
     * Turn on or off retrieving of Eve API data by this retriever.
     *
     * Allows class to stay registered for events but be enabled or disabled during runtime.
     *
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    public function setRetrieve(bool $value = true): self
    {
        $this->retrieve = $value;
        return $this;
    }
    /**
     * Returns current cache path.
     *
     * @return string
     * @throws \LogicException
     */
    private function getCachePath(): string
    {
        if (null === $this->cachePath || '' === $this->cachePath) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new \LogicException($mess);
        }
        return $this->cachePath;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return string|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getXmlFileContents(EveApiReadWriteInterface $data)
    {
        // BaseSection/ApiHash.xml
        $cacheFile = sprintf('%s%s/%s%s.xml',
            $this->getCachePath(),
            ucfirst($data->getEveApiSectionName()),
            ucfirst($data->getEveApiName()),
            $data->getHash());
        if (false === $xml = $this->safeFileRead($cacheFile)) {
            $messagePrefix = sprintf('Failed to retrieve XML file %s during the retrieval of', $cacheFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        if ('' === $xml) {
            $messagePrefix = sprintf('Received an empty XML file %s during the retrieval of', $cacheFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        $data->setEveApiXml($xml);
        if ($this->isExpired($data)) {
            $this->deleteWithRetry($cacheFile);
            $data->setEveApiXml('');
            return false;
        }
        $messagePrefix = sprintf('Using cached XML file %s during the retrieval of', $cacheFile);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($messagePrefix, $data));
        return $xml;
    }
    /**
     * Enforces minimum 5 minute cache time and does some basic checks to see if XML DateTimes are valid.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function isExpired(EveApiReadWriteInterface $data): bool
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        try {
            $simple = new \SimpleXMLElement($data->getEveApiXml());
        } catch (\Exception $exc) {
            $messagePrefix = 'The XML cause SimpleXMLElement exception during the retrieval of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            $this->checkLibXmlErrors($data, $this->getYem());
            libxml_use_internal_errors(false);
            return true;
        }
        libxml_use_internal_errors(false);
        /** @noinspection PhpUndefinedFieldInspection */
        if (null === $currentTime = $simple->currentTime[0]) {
            $messagePrefix = 'Cached XML file missing required currentTime element during the retrieval of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return true;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        if (null === $cachedUntil = $simple->cachedUntil[0]) {
            $messagePrefix = 'Cached XML file missing required cachedUntil element during the retrieval of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return true;
        }
        $eveFormat = 'Y-m-d H:i:sP';
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $current = \DateTimeImmutable::createFromFormat($eveFormat, $currentTime . '+00:00');
        $until = \DateTimeImmutable::createFromFormat($eveFormat, $cachedUntil . '+00:00');
        if (false === $now || false === $current || false === $until) {
            $messagePrefix = 'Failed to get DateTime instance for "now" or currentTime or cachedUntil during the retrieval of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $this->createEveApiMessage($messagePrefix, $data));
            return true;
        }
        // At minimum use cached XML for 5 minutes.
        if ($now <= $current->add(new \DateInterval('PT5M'))) {
            return false;
        }
        return ($until <= $now);
    }
    /**
     * @return bool
     */
    private function shouldRetrieve(): bool
    {
        return $this->retrieve;
    }
    /**
     * @var string $cachePath
     */
    private $cachePath;
    /**
     * @var bool $retrieve
     */
    private $retrieve = false;
}
