<?php
declare(strict_types = 1);
/**
 * Contains CommonEveApiTrait trait.
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
namespace Yapeal\EveApi;

use Monolog\Logger;
use PDO;
use Yapeal\CommonToolsTrait;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait CommonEveApiTrait
 */
trait CommonEveApiTrait
{
    use CommonToolsTrait, EveApiEventEmitterTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function oneShot(EveApiReadWriteInterface $data, MediatorInterface $yem): bool
    {
        if (!$this->gotApiLock($data, $yem)) {
            return false;
        }
        if ($this->cachedUntilIsNotExpired($data, $yem)) {
            return true;
        }
        $result = $this->processEvents($data, $yem);
        if ($result) {
            $this->updateCachedUntil($data, $yem);
            $this->emitEvents($data, 'end');
        }
        $this->releaseApiLock($data, $yem);
        return $result;
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
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    public function startEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        try {
            $records = $this->getActive($data, $yem);
        } catch (\PDOException $exc) {
            $mess = 'Could NOT get a list of active owners during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::WARNING,
                $this->createEveApiMessage($mess, $data),
                ['exception' => $exc]);
            return $event;
        }
        if (0 === count($records)) {
            $mess = 'No active owners found during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
            $this->emitEvents($data, 'end');
            return $event->setHandledSufficiently();
        }
        if (0 !== count($this->accountKeys)) {
            $records = $this->processAccountKeys($records);
        }
        foreach ($records as $arguments) {
            $aClone = clone $data;
            if (false !== $arguments) {
                $aClone->setEveApiArguments($arguments);
                if (0 === strpos($data->getEveApiName(), 'Wallet')) {
                    $aClone->addEveApiArgument('rowCount', '2560');
                }
            }
            if ($this->oneShot($aClone, $yem)) {
                $event->setHandledSufficiently();
            }
        }
        return $event;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function cachedUntilIsNotExpired(EveApiReadWriteInterface $data, MediatorInterface $yem): bool
    {
        $sql = $this->getCsq()
            ->getCachedUntilExpires($data->hasEveApiArgument('accountKey') ? (int)$data->getEveApiArgument('accountKey') : 0,
                $data->getEveApiName(),
                $this->extractOwnerID($data->getEveApiArguments()));
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        try {
            $expires = $this->getPdo()
                ->query($sql)
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $exc) {
            $mess = 'Could NOT query cache expired during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            return false;
        }
        if (0 === count($expires)) {
            $mess = 'No cached until row found during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        if (1 < count($expires)) {
            $mess = 'Multiple cached until rows found during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $this->createEveApiMessage($mess, $data));
            return false;
        }
        if (strtotime($expires[0]['expires'] . '+00:00') < time()) {
            $mess = 'Expired cached until row found during the processing of';
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->createEveApiMessage($mess, $data));
            return false;
        }
        return true;
    }
    /**
     * @param string[] $candidates
     *
     * @return int
     */
    protected function extractOwnerID(array $candidates): int
    {
        foreach (['corporationID', 'characterID', 'keyID'] as $item) {
            if (array_key_exists($item, $candidates)) {
                return (int)$candidates[$item];
            }
        }
        return 0;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return array
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function getActive(EveApiReadWriteInterface $data, MediatorInterface $yem)
    {
        switch (strtolower($data->getEveApiSectionName())) {
            case 'account':
                if ('APIKeyInfo' === $data->getEveApiName()) {
                    $sql = $this->getCsq()
                        ->getActiveRegisteredKeys();
                    break;
                }
                $sql = $this->getCsq()
                    ->getActiveRegisteredAccountStatus($this->getMask());
                break;
            case 'char':
                if ('MailBodies' === $data->getEveApiName()) {
                    $sql = $this->getCsq()
                        ->getActiveMailBodiesWithOwnerID((int)$data->getEveApiArgument('characterID'));
                    break;
                }
                $sql = $this->getCsq()
                    ->getActiveRegisteredCharacters($this->getMask());
                break;
            case 'corp':
                if ('StarbaseDetails' === $data->getEveApiName()) {
                    $sql = $this->getCsq()
                        ->getActiveStarbaseTowers($this->getMask(), (int)$data->getEveApiArgument('corporationID'));
                    break;
                }
                $sql = $this->getCsq()
                    ->getActiveRegisteredCorporations($this->getMask());
                break;
            default:
                return [false];
        }
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        return $this->getPdo()
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * @return int
     */
    protected function getMask(): int
    {
        return $this->mask;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function gotApiLock(EveApiReadWriteInterface $data, MediatorInterface $yem): bool
    {
        $sql = $this->getCsq()
            ->getApiLock(crc32($data->getHash()));
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $context = [];
        $success = false;
        try {
            $success = (bool)$this->getPdo()
                ->query($sql)
                ->fetchColumn();
        } catch (\PDOException $exc) {
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Got lock during the processing of' : 'Could NOT get lock during the processing of';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
        return $success;
    }
    /**
     * Used to make duplicate records for each accountKey.
     *
     * Eve APIs like the corp accountBalance, walletJournal, and walletTransactions are all per wallet as seen in game
     * so they have to be processed for each of the 'accountKey' to the Eve API servers. Currently that means 8 plus the
     * faction warfare/console game wallet for those corps involved with that part of the game.
     *
     * The same APIs for chars allow accountKey as well but they currently only have the one wallet 1000 so it could be
     * considered optional for them but CCP may decide to change that in the future so Yapeal uses it with them as well.
     *
     * @param array $records
     *
     * @return array
     */
    protected function processAccountKeys(array $records): array
    {
        $replacements = [];
        foreach ($records as $arguments) {
            $newArgs = $arguments;
            foreach ($this->accountKeys as $accountKey) {
                $newArgs['accountKey'] = $accountKey;
                $replacements[] = $newArgs;
            }
        }
        return $replacements;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function processEvents(EveApiReadWriteInterface $data, MediatorInterface $yem): bool
    {
        $eventSuffixes = ['retrieve', 'transform', 'validate', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            if (false === $this->emitEvents($data, $eventSuffix)) {
                return false;
            }
            if ('' === $data->getEveApiXml()) {
                if ($data->hasEveApiArgument('accountKey') && '10000' === $data->getEveApiArgument('accountKey')
                    && 'corp' === strtolower($data->getEveApiSectionName())
                ) {
                    $mess = 'No faction warfare account data during the processing of';
                    $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
                    return false;
                }
                $yem->triggerLogEvent('Yapeal.Log.log',
                        Logger::INFO,
                        $this->getEmptyXmlDataMessage($data, $eventSuffix));
                return false;
            }
        }
        return true;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function releaseApiLock(EveApiReadWriteInterface $data, MediatorInterface $yem): bool
    {
        $sql = $this->getCsq()
            ->getApiLockRelease(crc32($data->getHash()));
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $context = [];
        $success = false;
        try {
            $success = (bool)$this->getPdo()
                ->query($sql)
                ->fetchColumn();
        } catch (\PDOException $exc) {
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Released lock for' : 'Could NOT release lock for';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
        return $success;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param MediatorInterface        $yem
     *
     * @return static Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function updateCachedUntil(EveApiReadWriteInterface $data, MediatorInterface $yem)
    {
        if ('' === $data->getEveApiXml()) {
            return $this;
        }
        $currentTime = (string)(new \SimpleXMLElement($data->getEveApiXml()))->currentTime[0];
        if ('' === $currentTime) {
            return $this;
        }
        $dateTime = gmdate('Y-m-d H:i:s', strtotime($currentTime . '+00:00') + $data->getCacheInterval());
        $row = [
            'accountKey' => $data->hasEveApiArgument('accountKey') ? $data->getEveApiArgument('accountKey') : '0',
            'apiName' => $data->getEveApiName(),
            'expires' => $dateTime,
            'ownerID' => $this->extractOwnerID($data->getEveApiArguments()),
            'sectionName' => $data->getEveApiSectionName()
        ];
        $sql = $this->getCsq()
            ->getUpsert('utilCachedUntil', array_keys($row), 1);
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $pdo = $this->getPdo();
        $pdo->beginTransaction();
        $context = [];
        $success = false;
        try {
            $pdo->prepare($sql)
                ->execute(array_values($row));
            $pdo->commit();
            $success = true;
        } catch (\PDOException $exc) {
            $pdo->rollBack();
            $context = ['exception' => $exc];
        }
        $mess = $success ? 'Updated cached until date/time of' : 'Could NOT update cached until date/time of';
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data), $context);
        return $this;
    }
    /**
     * @var array $accountKey
     */
    protected $accountKeys = [];
    /**
     * @var int $mask
     */
    protected $mask;
}
