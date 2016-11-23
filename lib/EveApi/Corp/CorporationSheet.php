<?php
declare(strict_types = 1);
/**
 * Contains class CorporationSheet.
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

use Yapeal\EveApi\CommonEveApiTrait;
use Yapeal\Event\EveApiPreserverInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class CorporationSheet.
 */
class CorporationSheet implements EveApiPreserverInterface
{
    use CommonEveApiTrait, PreserverTrait;

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 8;
        $this->preserveTos = [
            'preserveToCorporationSheet',
            'preserverToDivisions',
            'preserverToLogo',
            'preserverToWalletDivisions'
        ];
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function preserverToCorporationSheet(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpCorporationSheet';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'allianceID' => '0',
            'allianceName' => '',
            'ceoID' => null,
            'ceoName' => null,
            'corporationID' => $ownerID,
            'corporationName' => null,
            'description' => null,
            'factionID' => '0',
            'factionName' => '',
            'memberCount' => null,
            'memberLimit' => '0',
            'shares' => null,
            'stationID' => null,
            'stationName' => null,
            'taxRate' => null,
            'ticker' => null,
            'url' => null
        ];
        $xPath = '//result/child::*[not(*|@*|self::dataTime)]';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->valuesPreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function preserverToDivisions(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpDivisions';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'accountKey' => null,
            'description' => null
        ];
        $xPath = '//divisions/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function preserverToLogo(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpLogo';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'color1' => null,
            'color2' => null,
            'color3' => null,
            'graphicID' => null,
            'shape1' => null,
            'shape2' => null,
            'shape3' => null
        ];
        $xPath = '//logo/*';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->valuesPreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function preserverToWalletDivisions(EveApiReadWriteInterface $data)
    {
        $tableName = 'corpWalletDivisions';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'accountKey' => null,
            'description' => null
        ];
        $xPath = '//walletDivisions/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
    /**
     * Special override to work around bug in Eve API server when including both KeyID and corporationID.
     *
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function processEvents(EveApiReadWriteInterface $data): bool
    {
        $corpID = 0;
        $eventSuffixes = ['retrieve', 'transform', 'validate', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            if ('retrieve' === $eventSuffix) {
                $corp = $data->getEveApiArguments();
                $corpID = $corp['corporationID'];
                // Can NOT include corporationID or will only get public info.
                if (array_key_exists('keyID', $corp)) {
                    unset($corp['corporationID']);
                    $data->setEveApiArguments($corp);
                }
            }
            if (false === $this->emitEvents($data, $eventSuffix)) {
                return false;
            }
            if ('retrieve' === $eventSuffix) {
                $data->addEveApiArgument('corporationID', (string)$corpID);
            }
            if (false === $data->getEveApiXml()) {
                if ($data->hasEveApiArgument('accountKey') && '10000' === $data->getEveApiArgument('accountKey')
                    && 'corp' === strtolower($data->getEveApiSectionName())
                ) {
                    $mess = 'No faction warfare account data in';
                    $this->getYem()
                        ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $this->createEveApiMessage($mess, $data));
                    return false;
                }
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log',
                        Logger::INFO,
                        $this->getEmptyXmlDataMessage($data, $eventSuffix));
                return false;
            }
        }
        return true;
    }
}
