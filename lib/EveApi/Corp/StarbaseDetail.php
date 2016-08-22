<?php
declare(strict_types = 1);
/**
 * Contains class StarbaseDetail.
 *
 * PHP version 7.0+
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\EveApi\Corp;

use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class StarbaseDetail.
 */
class StarbaseDetail extends CorpSection
{
    use PreserverTrait;

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 131072;
        $this->preserveTos = [
            'preserveToCombatSettings',
            'preserveToFuel',
            'preserveToGeneralSettings',
            'preserveToStarbaseDetail'
        ];
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
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    public function startEveApi(EveApiEventInterface $event, string $eventName, MediatorInterface $yem)
    {
        if (!$this->hasYem()) {
            $this->setYem($yem);
        }
        $data = $event->getData();
        $apiName = $data->getEveApiName();
        $data->setEveApiName('StarbaseList');
        // Insure Starbase list has already been updated first so we have current list to get details with.
        $this->emitEvents($data, 'start');
        $data->setEveApiName($apiName)
            ->setEveApiXml('');
        return parent::startEveApi($event->setData($data), $eventName, $yem);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \LogicException
     */
    protected function preserveToCombatSettings(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpCombatSettings';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $starbaseID = $data->getEveApiArgument('itemID');
        $sql = $this->getCsq()
            ->getDeleteFromStarbaseDetailTables($tableName, $ownerID, $starbaseID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'itemID' => $starbaseID,
            'onAggressionEnabled' => '0',
            'onCorporationWarEnabled' => '0',
            'onStandingDropStanding' => '0',
            'onStatusDropEnabled' => '0',
            'onStatusDropStanding' => '0',
            'ownerID' => $ownerID,
            'useStandingsFromOwnerID' => '0'
        ];
        $xPath = '//combatSettings/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \LogicException
     */
    protected function preserveToFuel(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpFuel';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $starbaseID = $data->getEveApiArgument('itemID');
        $sql = $this->getCsq()
            ->getDeleteFromStarbaseDetailTables($tableName, $ownerID, $starbaseID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'itemID' => $starbaseID,
            'ownerID' => $ownerID,
            'quantity' => '0',
            'typeID' => '0'
        ];
        $xPath = '//fuel/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \LogicException
     */
    protected function preserveToGeneralSettings(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpGeneralSettings';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $starbaseID = $data->getEveApiArgument('itemID');
        $sql = $this->getCsq()
            ->getDeleteFromStarbaseDetailTables($tableName, $ownerID, $starbaseID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'allowAllianceMembers' => '0',
            'allowCorporationMembers' => '0',
            'deployFlags' => '0',
            'itemID' => $starbaseID,
            'ownerID' => $ownerID,
            'usageFlags' => '0'
        ];
        $xPath = '//generalSettings/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \LogicException
     */
    protected function preserveToStarbaseDetail(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpStarbaseDetail';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $starbaseID = $data->getEveApiArgument('itemID');
        $sql = $this->getCsq()
            ->getDeleteFromStarbaseDetailTables($tableName, $ownerID, $starbaseID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'itemID' => $starbaseID,
            'onlineTimestamp' => '1970-01-01 00:00:01',
            'state' => '0',
            'stateTimestamp' => '1970-01-01 00:00:01'
        ];
        $xPath = '//result/child::*[not(*|@*|self::dataTime)]';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->valuesPreserveData($elements, $columnDefaults, $tableName);
    }
}
