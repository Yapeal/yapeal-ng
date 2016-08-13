<?php
declare(strict_types = 1);
/**
 * Contains CachePreserver class.
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
use Yapeal\Event\EveApiPreserverInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class CachePreserver
 */
class CachePreserver implements EveApiPreserverInterface
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait;
    /**
     * @param string|null $cachePath
     * @param bool        $preserve
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function __construct(string $cachePath = null, bool $preserve = false)
    {
        $this->setCachePath($cachePath)
            ->setPreserve($preserve);
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \LogicException
     */
    public function preserveEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        if (!$this->shouldPreserve()) {
            return $event;
        }
        $data = $event->getData();
        $this->setYem($yem);
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        // BaseSection/ApiHash.xml
        $cacheFile = sprintf('%1$s%2$s/%3$s%4$s.xml',
            $this->getCachePath(),
            ucfirst($data->getEveApiSectionName()),
            ucfirst($data->getEveApiName()),
            $data->getHash());
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        // Insures retriever never see partly written file by deleting old file and using temp file for writing.
        if (false === $this->safeFileWrite($xml, $cacheFile)) {
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string|null $value
     *
     * @return self Fluent interface.
     * @throws \DomainException
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
        if ('' === $this->cachePath) {
            $mess = 'Cache path can NOT be empty';
            throw new \DomainException($mess);
        }
        $this->cachePath = $this->getFpn()
            ->normalizePath($value);
        return $this;
    }
    /**
     * Turn on or off preserving of Eve API data by this preserver.
     *
     * Allows class to stay registered for events but be enabled or disabled during runtime.
     *
     * @param boolean $value
     *
     * @return EveApiPreserverInterface|CachePreserver Fluent interface.
     */
    public function setPreserve(bool $value = true): self
    {
        $this->preserve = (boolean)$value;
        return $this;
    }
    /**
     * @return string
     * @throws \LogicException
     */
    protected function getCachePath()
    {
        if (null === $this->cachePath) {
            $mess = ' Trying to use cachePath before it was set';
            throw new \LogicException($mess);
        }
        return $this->cachePath;
    }
    /**
     * @return boolean
     */
    private function shouldPreserve()
    {
        return $this->preserve;
    }
    /**
     * @var string $cachePath
     */
    private $cachePath;
    /**
     * @var bool $preserve
     */
    private $preserve = false;
}
