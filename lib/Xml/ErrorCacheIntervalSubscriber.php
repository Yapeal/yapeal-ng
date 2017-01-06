<?php
declare(strict_types = 1);
/**
 * Contains class ErrorCacheIntervalSubscriber.
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
namespace Yapeal\Xml;

use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class ErrorCacheIntervalSubscriber.
 */
class ErrorCacheIntervalSubscriber
{
    use EveApiEventEmitterTrait;
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
    public function processXmlError(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        $simple = new \SimpleXMLElement($data->getEveApiXml());
        /** @noinspection PhpUndefinedFieldInspection */
        $errorText = (string)$simple->error[0];
        /** @noinspection PhpUndefinedFieldInspection */
        $code = (int)$simple->error[0]['code'] ?? 0;
        $mess = sprintf('Received from the Eve API server an XML error (%s) "%s" response during the validation of',
            $code,
            $errorText);
        $mess = $this->createEveApiMessage($mess, $data);
        if ($code < 200) {
            if (false !== strpos($errorText, 'retry after')) {
                $data->setCacheInterval(strtotime(substr($errorText, -19) . '+00:00') - time());
            }
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName));
        } elseif ($code < 300) {
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::ERROR, $this->createEventMessage($mess, $data, $eventName));
            $data->setCacheInterval(86400);
        } elseif ($code > 903 && $code < 905) {
            // Major application or Yapeal error.
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::ALERT, $this->createEventMessage($mess, $data, $eventName));
            $data->setCacheInterval(86400);
        } else {
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEventMessage($mess, $data, $eventName));
            $data->setCacheInterval(300);
        }
        // Cache error XML.
        $apiName = $data->getEveApiName();
        $data->setEveApiName('Error_' . $apiName);
        $this->emitEvents($data, 'preserve', 'Yapeal.Xml.Error');
        $data->setEveApiName($apiName);
        return $event->setData($data)
            ->setHandledSufficiently();
    }
}
