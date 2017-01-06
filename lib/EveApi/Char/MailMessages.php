<?php
declare(strict_types = 1);
/**
 * Contains class MailMessages.
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\EveApi\Char;

use Yapeal\Event\EveApiPreserverInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class MailMessages.
 */
class MailMessages extends CharSection implements EveApiPreserverInterface
{
    use PreserverTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 2048;
        $this->preserveTos = [
            'preserveToMailMessages'
        ];
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return void
     * @throws \LogicException
     */
    protected function preserveToMailMessages(EveApiReadWriteInterface $data)
    {
        $tableName = 'charMailMessages';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, 'sql - ' . $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'messageID' => null,
            'ownerID' => $ownerID,
            'senderID' => null,
            'senderName' => null,
            'senderTypeID' => null,
            'sentDate' => null,
            'title' => null,
            'toCharacterIDs' => null,
            'toCorpOrAllianceID' => '0',
            'toListID' => null
        ];
        $xPath = '//messages/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
    }
}
