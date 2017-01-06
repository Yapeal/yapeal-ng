<?php
declare(strict_types = 1);
/**
 * Contains Validator class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2017 Michael Cummings
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
 * @copyright 2015-2017 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsd;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Exception\YapealFileSystemException;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\FileSystem\SafeFileHandlingTrait;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\LibXmlChecksTrait;

/**
 * Class Validator
 */
class Validator implements ValidatorInterface, YEMAwareInterface
{
    use EveApiEventEmitterTrait;
    use LibXmlChecksTrait;
    use RelativeFileSearchTrait;
    use SafeFileHandlingTrait;
    /**
     * Constructor.
     *
     * @param string $dir Base directory where Eve API XSD files can be found.
     */
    public function __construct(string $dir = __DIR__)
    {
        $this->setRelativeBaseDir($dir . '/');
    }
    /**
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
    public function validateEveApi(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        if ('' === $xml = $data->getEveApiXml()) {
            $messagePrefix = 'Given empty XML during the validation of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return $event;
        }
        if (false !== strpos($xml, '<!DOCTYPE html')) {
            $messagePrefix = 'Received HTML error doc instead of XML data during the validation of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            // Cache received error html.
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Invalid_' . $apiName);
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            return $event;
        }
        if (false === $xsdContents = $this->getXsdFileContents($data)) {
            return $event;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $dom = $this->getDom();
        if (false === $dom->loadXML($xml)) {
            $messagePrefix = 'DOM could not load XML during the validation of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            $this->checkLibXmlErrors($data, $yem);
            libxml_use_internal_errors(false);
            return $event;
        }
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        if (false === @$dom->schemaValidateSource($xsdContents)) {
            $messagePrefix = 'DOM schema could not validate XML during the validation of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            // Cache error causing XML.
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Invalid_' . $apiName);
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            $this->checkLibXmlErrors($data, $yem);
            libxml_use_internal_errors(false);
            return $event;
        }
        if (false === $this->checkForValidDateTimes($dom, $data)) {
            return $event;
        }
        libxml_use_internal_errors(false);
        $messagePrefix = 'Successfully validated the XML during the validation of';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($messagePrefix, $data));
        // Check for XML error element.
        if (false !== strpos($xml, '<error ')) {
            $this->emitEvents($data, 'start', 'Yapeal.Xml.Error');
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param \DOMDocument             $dom
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function checkForValidDateTimes(\DOMDocument $dom, EveApiReadWriteInterface $data)
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $simple = simplexml_import_dom($dom);
        $eveFormat = 'Y-m-d H:i:sP';
        $current = \DateTimeImmutable::createFromFormat($eveFormat, $simple->currentTime[0] . '+00:00');
        $until = \DateTimeImmutable::createFromFormat($eveFormat, $simple->cachedUntil[0] . '+00:00');
        if ($until <= $current) {
            $messagePrefix = sprintf('CachedUntil is invalid was given %s and currentTime is %s during the validation of',
                $until->format($eveFormat),
                $current->format($eveFormat));
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            $mess = 'Please report the above logged error to CCP so they can fixed it';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return false;
        }
        // Current plus a day.
        if ($until > $current->add(new \DateInterval('P1D'))) {
            $messagePrefix = sprintf('CachedUntil is excessively long was given %s and it is currently %s during the'
                . ' validation of',
                $until->format($eveFormat),
                $current->format($eveFormat));
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        return true;
    }
    /**
     * @return \DOMDocument
     */
    private function getDom(): \DOMDocument
    {
        if (null === $this->dom) {
            $this->dom = new \DOMDocument();
        }
        return $this->dom;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function getXsdFileContents(EveApiReadWriteInterface $data)
    {
        try {
            $xsdFile = $this->findRelativeFileWithPath(ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'xsd');
        } catch (YapealFileSystemException $exc) {
            $messagePrefix = 'Failed to find accessible XSD file during the validation of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            return false;
        }
        $contents = $this->safeFileRead($xsdFile);
        if (false === $contents) {
            $messagePrefix = sprintf('Failed to read XSD file %s during the validation of', $xsdFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        if ('' === $contents) {
            $messagePrefix = sprintf('Received an empty XSD file %s during the validation of', $xsdFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($messagePrefix, $data));
            return false;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        try {
            if (false !== new \SimpleXMLElement($contents)) {
                $messagePrefix = sprintf('Using XSD file %s during the validation of', $xsdFile);
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log',
                        Logger::INFO,
                        $this->createEveApiMessage($messagePrefix, $data));
                return $contents;
            }
        } catch (\Exception $exc) {
            $messagePrefix = sprintf('SimpleXMLElement exception caused by XSD file %s during the validation of',
                $xsdFile);
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($messagePrefix, $data),
                    ['exception' => $exc]);
            $this->checkLibXmlErrors($data, $this->getYem());
        }
        libxml_use_internal_errors(false);
        return false;
    }
    /**
     * @var \DOMDocument $dom
     */
    private $dom;
}
