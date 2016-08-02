<?php
declare(strict_types=1);
/**
 * Contains class CharacterSheet.
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

use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class CharacterSheet
 */
class CharacterSheet extends CharSection
{
    use PreserverTrait;
    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 8;
        $this->preserveTos = [
            'preserveToAttributes',
            'preserveToCertificates',
            'preserveToCharacterSheet',
            'preserveToCorporationRoles',
            'preserveToCorporationRolesAtBase',
            'preserveToCorporationRolesAtHQ',
            'preserveToCorporationRolesAtOther',
            'preserveToCorporationTitles',
            'preserveToImplants',
            'preserveToJumpCloneImplants',
            'preserveToJumpClones',
            'preserveToSkills'
        ];
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToAttributes(EveApiReadWriteInterface $data)
    {
        $tableName = 'charAttributes';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'charisma' => null,
            'intelligence' => null,
            'memory' => null,
            'ownerID' => $ownerID,
            'perception' => null,
            'willpower' => null
        ];
        $xPath = '//attributes/*';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->valuesPreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCertificates(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCertificates';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'certificateID' => null,
            'ownerID' => $ownerID
        ];
        $xPath = '//certificates/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCharacterSheet(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCharacterSheet';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'allianceID' => null,
            'allianceName' => '',
            'ancestry' => null,
            'ancestryID' => null,
            'balance' => '0.0',
            'bloodLine' => null,
            'bloodLineID' => null,
            'characterID' => null,
            'cloneJumpDate' => '1970-01-01 00:00:01',
            'cloneName' => '',
            'cloneSkillPoints' => 0,
            'cloneTypeID' => 0,
            'corporationID' => null,
            'corporationName' => '',
            'DoB' => null,
            'factionID' => 0,
            'factionName' => '',
            'freeRespecs' => null,
            'freeSkillPoints' => 0,
            'gender' => null,
            'homeStationID' => 0,
            'jumpActivation' => '1970-01-01 00:00:01',
            'jumpFatigue' => '1970-01-01 00:00:01',
            'jumpLastUpdate' => '1970-01-01 00:00:01',
            'lastRespecDate' => '1970-01-01 00:00:01',
            'lastTimedRespec' => '1970-01-01 00:00:01',
            'name' => '',
            'ownerID' => $ownerID,
            'race' => null,
            'remoteStationDate' => '1970-01-01 00:00:01'
        ];
        $xPath = '//result/child::*[not(*|@*|self::dataTime)]';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->valuesPreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCorporationRoles(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCorporationRoles';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => ''
        ];
        $xPath = '//corporationRoles/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCorporationRolesAtBase(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCorporationRolesAtBase';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => ''
        ];
        $xPath = '//corporationRolesAtBase/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCorporationRolesAtHQ(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCorporationRolesAtHQ';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => ''
        ];
        $xPath = '//corporationRolesAtHQ/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCorporationRolesAtOther(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCorporationRolesAtOther';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => ''
        ];
        $xPath = '//corporationRolesAtOther/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToCorporationTitles(EveApiReadWriteInterface $data)
    {
        $tableName = 'charCorporationTitles';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'titleID' => null,
            'titleName' => ''
        ];
        $xPath = '//corporationTitles/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToImplants(EveApiReadWriteInterface $data)
    {
        $tableName = 'charImplants';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'ownerID' => $ownerID,
            'typeID' => null,
            'typeName' => ''
        ];
        $xPath = '//implants/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToJumpCloneImplants(EveApiReadWriteInterface $data)
    {
        $tableName = 'charJumpCloneImplants';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'jumpCloneID' => null,
            'ownerID' => $ownerID,
            'typeID' => null,
            'typeName' => ''
        ];
        $xPath = '//jumpCloneImplants/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToJumpClones(EveApiReadWriteInterface $data)
    {
        $tableName = 'charJumpClones';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'cloneName' => '',
            'jumpCloneID' => null,
            'locationID' => null,
            'ownerID' => $ownerID,
            'typeID' => null
        ];
        $xPath = '//jumpClones/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToSkills(EveApiReadWriteInterface $data)
    {
        $tableName = 'charSkills';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'level' => null,
            'ownerID' => $ownerID,
            'published' => null,
            'skillpoints' => null,
            'typeID' => null
        ];
        $xPath = '//skills/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
}
