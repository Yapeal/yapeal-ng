<?php
/**
 * Contains AccountKeyTrait trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
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
 * @copyright 2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi;

use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait AccountKeyTrait
 */
trait AccountKeyTrait
{
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws \LogicException
     */
    public function oneShot(EveApiReadWriteInterface $data)
    {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $result = true;
        $eventSuffixes = ['retrieve', 'transform', 'validate', 'cache', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            if (false === $this->emitEvents($data, $eventSuffix)) {
                $result = false;
                break;
            }
            if (false === $data->getEveApiXml()) {
                /**
                 * @type MediatorInterface $yem
                 */
                $yem = $this->getYem();
                if ('10000' === $data->getEveApiArgument('accountKey')
                    && 'corp' === strtolower($data->getEveApiSectionName())
                ) {
                    $mess = 'No faction warfare account data in';
                    $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
                    break;
                }
                $yem->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::NOTICE,
                    $this->getEmptyXmlDataMessage($data, $eventSuffix)
                );
                $result = false;
                break;
            }
        }
        return $result;
    }
    /**
     * @param EveApiEventInterface $event
     * @param string               $eventName
     * @param MediatorInterface    $yem
     *
     * @return EveApiEventInterface
     * @throws \LogicException
     */
    public function startEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent(
            'Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__)
        );
        $active = method_exists($this, 'getActive') ? $this->getActive() : [[null]];
        if (0 === count($active)) {
            $mess = 'No active owners found for';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
            $this->emitEvents($data, 'end');
            return $event->setHandledSufficiently();
        }
        $untilInterval = $data->getCacheInterval();
        foreach ($active as $arguments) {
            $ownerID = $this->extractOwnerID($arguments);
            if ($this->cacheNotExpired($data, $ownerID)) {
                continue;
            }
            $arguments['rowCount'] = '2560';
            foreach ($this->accountKeys as $accountKey) {
                $arguments['accountKey'] = $accountKey;
                // Set arguments, reset interval, and clear xml data.
                $data->setEveApiArguments($arguments)
                    ->setCacheInterval($untilInterval)
                    ->setEveApiXml();
                if (!$this->oneShot($data)) {
                    $this->releaseApiLock($data);
                    continue 2;
                }
                $this->emitEvents($data, 'end');
            }
            $event->setHandledSufficiently();
            $this->updateCachedUntil($data, $ownerID);
            $this->releaseApiLock($data);
        }
        return $event;
    }
    /**
     * @type array $accountKeys
     */
    protected $accountKeys;
}
