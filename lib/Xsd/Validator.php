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
 * Copyright (C) 2015-2016 Michael Cummings
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
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsd;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Exception\YapealFileSystemException;
use Yapeal\FileSystem\RelativeFileSearchTrait;
use Yapeal\Log\Logger;

/**
 * Class Validator
 */
class Validator
{
    use EveApiEventEmitterTrait, RelativeFileSearchTrait;
    /**
     * Constructor.
     *
     * @param string $dir Base directory where Eve API XSD files can be found.
     */
    public function __construct($dir = __DIR__)
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
     */
    public function validateEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        if (false === $data->getEveApiXml()) {
            return $event;
        }
        $htmlError = strpos($data->getEveApiXml(), '<!DOCTYPE html');
        if (false !== $htmlError) {
            $mess = 'Received HTML result from ';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $this->createEveApiMessage($mess, $data));
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Invalid_' . $apiName);
            // Cache error html.
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            return $event->setData($data);
        }
        try {
            $xsdName = $this->findRelativeFileWithPath(ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'xsd');
        } catch (YapealFileSystemException $exc) {
            $mess = 'Failed to find accessible XSD schema file during';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName),
                ['exception' => $exc]);
            return $event;
        }
        $mess = sprintf('Using %1$s file to validate', $xsdName);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($data->getEveApiXml());
        if (!$loaded || !$dom->schemaValidate($xsdName)) {
            /**
             * @var \libXMLError[] $errors
             */
            $errors = libxml_get_errors();
            if (0 !== count($errors)) {
                foreach ($errors as $error) {
                    if (null !== $error->message) {
                        $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $error->message);
                    }
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            libxml_clear_errors();
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Invalid_' . $apiName);
            // Cache error causing XML.
            $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
            $data->setEveApiName($apiName);
            $event->setData($data);
            return $event->setHandledSufficiently(false);
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        libxml_clear_errors();
        // Check for XML error element.
        if (false !== strpos($data->getEveApiXml(), '<error ')) {
            $this->emitEvents($data, 'start', 'Yapeal.Xml.Error');
            $event->setData($data);
            return $event->setHandledSufficiently(false);
        }
        return $event->setHandledSufficiently();
    }
}
