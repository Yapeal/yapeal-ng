<?php
/**
 * Contains WalletTransactions class.
 *
 * PHP version 5.4
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
namespace Yapeal\EveApi\Char;

use PDOException;
use Yapeal\EveApi\AccountKeyTrait;
use Yapeal\EveApi\CommonEveApiTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;

/**
 * Class WalletTransactions
 */
class WalletTransactions extends CharSection
{
    use PreserverTrait, AccountKeyTrait {
        AccountKeyTrait::oneShot insteadof CommonEveApiTrait;
        AccountKeyTrait::startEveApi insteadof CommonEveApiTrait;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 4194304;
        $this->accountKeys = ['1000'];
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
    public function preserveEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
        $this->getPdo()
            ->beginTransaction();
        try {
            $this->preserveToWalletTransactions($xml, $ownerID, $data->getEveApiArgument('accountKey'));
            $this->getPdo()
                ->commit();
        } catch (PDOException $exc) {
            $mess = 'Failed to upsert data of';
            $this->getYem()
                ->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]
                );
            $this->getPdo()
                ->rollBack();
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToWalletTransactions($xml, $ownerID, $accountKey)
    {
        $tableName = 'charWalletTransactions';
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerIDAndAccountKey($tableName, $ownerID, $accountKey);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'accountKey'           => $accountKey,
            'clientID'             => null,
            'clientName'           => '',
            'clientTypeID'         => null,
            'journalTransactionID' => null,
            'ownerID'              => $ownerID,
            'price'                => null,
            'quantity'             => null,
            'stationID'            => null,
            'stationName'          => '',
            'transactionDateTime'  => '1970-01-01 00:00:01',
            'transactionFor'       => null,
            'transactionID'        => null,
            'transactionType'      => null,
            'typeID'               => null,
            'typeName'             => ''
        ];
        $this->attributePreserveData($xml, $columnDefaults, $tableName, '//transactions/row');
        return $this;
    }
}
