<?php
/**
 * Contains Validator class.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xsd;

use DOMDocument;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
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
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        $fileName = $this->findEveApiFile($data->getEveApiSectionName(), $data->getEveApiName(), 'xsd');
        if ('' === $fileName) {
            return $event;
        }
        $oldErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $dom = new DOMDocument();
        $dom->loadXML($data->getEveApiXml());
        if (!$dom->schemaValidate($fileName)) {
            /**
             * @var array $errors
             */
            $errors = libxml_get_errors();
            if (0 !== count($errors)) {
                foreach ($errors as $error) {
                    $this->getYem()
                        ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $error->message);
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors($oldErrors);
            $apiName = $data->getEveApiName();
            $data->setEveApiName('Invalid_' . $apiName);
            // Cache error XML.
            $this->emitEvents($data, 'cache');
            $data->setEveApiName($apiName);
            return $event->setData($data);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($oldErrors);
        // Check for XML error element.
        if (false !== strpos($data->getEveApiXml(), '<error ')) {
            $this->emitEvents($data, 'error', 'Yapeal.Xml');
            return $event->setData($data);
        }
        return $event->setHandledSufficiently();
    }
}
