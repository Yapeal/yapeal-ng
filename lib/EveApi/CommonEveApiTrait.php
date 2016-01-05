<?php
/**
 * Contains CommonEveApiTrait trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi;

use Monolog\Logger;
use PDO;
use PDOException;
use SimpleXMLElement;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait CommonEveApiTrait
 */
trait CommonEveApiTrait
{
    use EveApiToolsTrait, EveApiEventEmitterTrait;
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
        $eventSuffixes = ['retrieve', 'transform', 'validate', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            if (false === $this->emitEvents($data, $eventSuffix)) {
                $result = false;
                break;
            }
            if (false === $data->getEveApiXml()) {
                $this->getYem()
                    ->triggerLogEvent(
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
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        $active = method_exists($this, 'getActive') ? $this->getActive($data) : [[null]];
        if (0 === count($active)) {
            $mess = 'No active owners found for';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
            $this->emitEvents($data, 'end');
            return $event->setHandledSufficiently();
        }
        $untilInterval = $data->getCacheInterval();
        foreach ($active as $arguments) {
            $ownerID = $this->extractOwnerID($arguments);
            if ($this->cacheNotExpired($data, $ownerID)) {
                continue;
            }
            // Set arguments, reset interval, and clear xml data.
            $data->setEveApiArguments($arguments)
                ->setCacheInterval($untilInterval)
                ->setEveApiXml();
            if (!$this->oneShot($data)) {
                $this->releaseApiLock($data);
                continue;
            }
            /** @noinspection DisconnectedForeachInstructionInspection */
            $this->emitEvents($data, 'end');
            /** @noinspection DisconnectedForeachInstructionInspection */
            $event->setHandledSufficiently();
            $this->updateCachedUntil($data, $ownerID);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $this->releaseApiLock($data);
        }
        return $event;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $ownerID
     *
     * @return bool
     * @throws \LogicException
     */
    protected function cacheNotExpired(EveApiReadWriteInterface $data, $ownerID = '0')
    {
        $sql = $this->getCsq()
            ->getUtilCachedUntilExpires(
                $data->getEveApiName(),
                $data->getEveApiSectionName(),
                $ownerID
            );
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            /**
             * @type \PDOStatement $stmt
             */
            $stmt = $this->getPdo()
                ->query($sql);
            $expires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get cache expired for';
            $this->getYem()
                ->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]
                );
            return false;
        }
        if (0 === count($expires)) {
            $mess = 'No UtilCachedUntil record found for';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        if (1 < count($expires)) {
            $mess = 'Multiple UtilCachedUntil records found for';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($mess, $data));
            return false;
        }
        if (strtotime($expires[0]['expires'] . '+00:00') < time()) {
            $mess = 'Expired UtilCachedUntil record found for';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        return true;
    }
    /**
     * @param string[] $candidates
     *
     * @return string
     * @throws \DomainException
     */
    protected function extractOwnerID(array $candidates)
    {
        foreach (['corporationID', 'characterID', 'keyID'] as $item) {
            if (array_key_exists($item, $candidates)) {
                return (string)$candidates[$item];
            }
        }
        return '0';
    }
    /**
     * @return int
     */
    protected function getMask()
    {
        return $this->mask;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @throws \LogicException
     * @return bool
     */
    protected function gotApiLock(EveApiReadWriteInterface $data)
    {
        $sql = $this->getCsq()
            ->getApiLock($data->getHash());
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $context = [];
        $success = false;
        try {
            /**
             * @type \PDOStatement $stmt
             */
            $stmt = $this->getPdo()
                ->query($sql);
            $success = (bool)$stmt->fetchColumn();
        } catch (PDOException $exc) {
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Got lock for' : 'Could NOT get lock for';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
        return $success;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @throws \LogicException
     * @return bool
     */
    protected function releaseApiLock(EveApiReadWriteInterface $data)
    {
        $sql = $this->getCsq()
            ->getApiLockRelease($data->getHash());
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $context = [];
        $success = false;
        try {
            /**
             * @type \PDOStatement $stmt
             */
            $stmt = $this->getPdo()
                ->query($sql);
            $success = (bool)$stmt->fetchColumn();
        } catch (PDOException $exc) {
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Released lock for' : 'Could NOT release lock for';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
        return $success;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $ownerID
     *
     * @throws \LogicException
     */
    protected function updateCachedUntil(EveApiReadWriteInterface $data, $ownerID)
    {
        if (false === $data->getEveApiXml()) {
            return;
        }
        $simple = new SimpleXMLElement($data->getEveApiXml());
        /** @noinspection PhpUndefinedFieldInspection */
        if (null === $simple->currentTime[0]) {
            return;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $dateTime = gmdate(
            'Y-m-d H:i:s',
            strtotime($simple->currentTime[0] . '+00:00') + $data->getCacheInterval()
        );
        $row = [$data->getEveApiName(), $dateTime, $ownerID, $data->getEveApiSectionName()];
        $sql = $this->getCsq()
            ->getUtilCachedUntilUpsert();
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $pdo = $this->getPdo();
        $pdo->beginTransaction();
        $context = [];
        $success = false;
        try {
            /**
             * @type \PDOStatement $stmt
             */
            $stmt = $pdo->prepare($sql);
            $stmt->execute($row);
            $pdo->commit();
            $success = true;
        } catch (PDOException $exc) {
            $pdo->rollBack();
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Updated cached until date/time of' : 'Could NOT update cached until date/time of';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
    }
    /**
     * @type int $mask
     */
    protected $mask;
}
