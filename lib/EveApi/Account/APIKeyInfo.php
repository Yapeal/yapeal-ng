<?php
declare(strict_types = 1);
/**
 * Contains APIKeyInfo class.
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
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Account;

use Yapeal\Event\EveApiPreserverInterface;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AccountSection implements EveApiPreserverInterface
{
    use PreserverTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->preserveTos = [
            'preserveToAPIKeyInfo',
            'preserveToCharacters',
            'preserveToKeyBridge'
        ];
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToAPIKeyInfo(EveApiReadWriteInterface $data)
    {
        $tableName = 'accountAPIKeyInfo';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $columnDefaults = [
            'accessMask' => null,
            'expires' => '2038-01-19 03:14:07',
            'keyID' => $ownerID,
            'type' => null
        ];
        $xPath = '//key';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToCharacters(EveApiReadWriteInterface $data)
    {
        $tableName = 'accountCharacters';
        $columnDefaults = [
            'allianceID' => null,
            'allianceName' => null,
            'characterID' => null,
            'characterName' => null,
            'corporationID' => null,
            'corporationName' => null,
            'factionID' => null,
            'factionName' => null
        ];
        $xPath = '//characters/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function preserveToKeyBridge(EveApiReadWriteInterface $data)
    {
        $tableName = 'accountKeyBridge';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $columnDefaults = ['keyID' => $ownerID, 'characterID' => null];
        $xPath = '//characters/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
}
