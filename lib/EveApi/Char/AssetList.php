<?php
declare(strict_types = 1);
/**
 * Contains class AssetList.
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
namespace Yapeal\EveApi\Char;

use Yapeal\CommonToolsInterface;
use Yapeal\EveApi\NestedSetTrait;
use Yapeal\Event\EveApiPreserverInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class AssetList.
 */
class AssetList extends CharSection implements CommonToolsInterface, EveApiPreserverInterface
{
    use PreserverTrait, NestedSetTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 2;
        $this->preserveTos = ['preserveToAssetList'];
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function preserveToAssetList(EveApiReadWriteInterface $data): self
    {
        $tableName = 'charAssetList';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'flag' => '0',
            'itemID' => null,
            'lft' => null,
            'lvl' => null,
            'locationID' => null,
            'ownerID' => $ownerID,
            'quantity' => '1',
            'rawQuantity' => '0',
            'rgt' => null,
            'singleton' => '0',
            'typeID' => null
        ];
        $xPath = '//row';
        $simple = new \SimpleXMLElement($data->getEveApiXml());
        /** @noinspection PhpUndefinedFieldInspection */
        if (0 !== $simple->result[0]->count()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $simple->result[0]->row[0]['itemID'] = $ownerID;
            /** @noinspection PhpUndefinedFieldInspection */
            $this->addNesting($simple->result[0]->row[0]);
        }
        $data->setEveApiXml($simple->asXML());
        $this->attributePreserveData($simple->xpath($xPath), $columnDefaults, $tableName);
        return $this;
    }
}
