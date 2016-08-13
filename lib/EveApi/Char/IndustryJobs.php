<?php
declare(strict_types = 1);
/**
 * Contains class IndustryJobs.
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
 * @copyright 2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Char;

use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class IndustryJobs
 */
class IndustryJobs extends CharSection
{
    use PreserverTrait;

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mask = 128;
        $this->preserveTos = [
            'preserveToIndustryJobs'
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
        $data->setEveApiName($apiName . 'History');
        // Insure history has already been updated first so current data overwrites old data.
        $this->emitEvents($data, 'start');
        $data->setEveApiName($apiName)
            ->setEveApiXml('');
        return parent::startEveApi($event->setData($data), $eventName, $yem);
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self Fluent interface.
     * @throws \LogicException
     */
    protected function preserveToIndustryJobs(EveApiReadWriteInterface $data)
    {
        $tableName = 'charIndustryJobs';
        $ownerID = $this->extractOwnerID($data->getEveApiArguments());
        $sql = $this->getCsq()
            ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $this->getPdo()
            ->exec($sql);
        $columnDefaults = [
            'activityID' => null,
            'blueprintID' => null,
            'blueprintLocationID' => null,
            'blueprintTypeID' => null,
            'blueprintTypeName' => '',
            'completedCharacterID' => null,
            'completedDate' => null,
            'cost' => null,
            'endDate' => null,
            'facilityID' => null,
            'installerID' => null,
            'installerName' => '',
            'jobID' => null,
            'licensedRuns' => null,
            'outputLocationID' => null,
            'ownerID' => $ownerID,
            'pauseDate' => null,
            'probability' => null,
            'productTypeID' => null,
            'productTypeName' => '',
            'runs' => null,
            'solarSystemID' => null,
            'solarSystemName' => '',
            'startDate' => null,
            'stationID' => null,
            'status' => null,
            'successfulRuns' => null,
            'teamID' => null,
            'timeInSeconds' => null
        ];
        $xPath = '//jobs/row';
        $elements = (new \SimpleXMLElement($data->getEveApiXml()))->xpath($xPath);
        $this->attributePreserveData($elements, $columnDefaults, $tableName);
        return $this;
    }
}
