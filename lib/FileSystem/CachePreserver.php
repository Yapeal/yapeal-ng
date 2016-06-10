<?php
/**
 * Contains CachePreserver class.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use DomainException;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use LogicException;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class CachePreserver
 */
class CachePreserver
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param string|null $cachePath
     *
     * @throws InvalidArgumentException
     * @throws DomainException
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
     * @throws LogicException
     */
    public function preserveEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        $data = $event->getData();
        $this->setYem($yem);
        $yem->triggerLogEvent(
            'Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__)
        );
        // BaseSection/ApiHash.xml
        $cacheFile = sprintf(
            '%1$s%2$s/%3$s%4$s.xml',
            $this->getCachePath(),
            ucfirst($data->getEveApiSectionName()),
            ucfirst($data->getEveApiName()),
            $data->getHash()
        );
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        // Insures retriever never see partly written file by deleting old file and using temp file for writing.
        if (false === $this->safeFileWrite($xml, $cacheFile, $yem)) {
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string|null $value
     *
     * @return self Fluent interface.
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function setCachePath($value = null)
    {
        if ($value === null) {
            $value = dirname(dirname(__DIR__)) . '/cache/';
        }
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string, but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        if ('' === $this->cachePath) {
            $mess = 'Cache path can NOT be empty';
            throw new DomainException($mess);
        }
        $this->cachePath = $this->getFpn()
            ->normalizePath($value);
        return $this;
    }
    /**
     * @return string
     * @throws LogicException
     */
    protected function getCachePath()
    {
        return $this->cachePath;
    }
    /**
     * @var string $cachePath
     */
    protected $cachePath;
}
