<?php
/**
 * Contains class AssetList.
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
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Corp;

use PDOException;
use SimpleXMLElement;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;

/**
 * Class AssetList.
 */
class AssetList extends CorpSection
{
    use PreserverTrait;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 2;
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
            $this->preserveToAssetList($xml, $ownerID);
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
     * @param SimpleXMLElement $row
     * @param int              $idx
     *
     * @return int
     */
    protected function addNesting(SimpleXMLElement $row, $idx = 0)
    {
        $row['lft'] = $idx;
        if ($row->count()) {
            $children = $row->children();
            foreach ($children as $descendant) {
                $idx = $this->addNesting($descendant, ++$idx);
            }
        }
        $row['rgt'] = ++$idx;
        return $idx;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToAssetList($xml, $ownerID)
    {
        $tableName = 'corpAssetList';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
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
        $simple = new \SimpleXMLElement($xml);
        /** @noinspection PhpUndefinedFieldInspection */
        if (0 !== $simple->result[0]->count()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $simple->result[0]->row[0]['itemID'] = $ownerID;
            /** @noinspection PhpUndefinedFieldInspection */
            $this->addNesting($simple->result[0]->row[0]);
        }
        return $this->attributePreserveData($simple->asXML(), $columnDefaults, $tableName);
    }
}
