<?php
/**
 * Contains CorpSection class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2015 Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Corp;

use LogicException;
use PDO;
use PDOException;
use Yapeal\EveApi\CommonEveApiTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class CorpSection
 */
class CorpSection
{
    use CommonEveApiTrait;
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
        $ownerID = $data->getEveApiArgument('corporationID');
        $pTo = 'preserveTo' . $data->getEveApiName();
        $this->getYem()
             ->triggerLogEvent(
                 'Yapeal.Log.log',
                 Logger::DEBUG,
                 $this->getReceivedEventMessage($data, $eventName, __CLASS__)
             );
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->{$pTo}($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $ownerID
            );
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess, ['exception' => $exc]);
            $this->getPdo()
                 ->rollBack();
            return $event;
        }
        return $event->setHandledSufficiently();
    }
    /**
     * @return array
     * @throws LogicException
     */
    protected function getActive()
    {
        $sql =
            $this->getCsq()
                 ->getActiveRegisteredCorporations($this->getMask());
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt =
                $this->getPdo()
                     ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active corporations';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            $mess = 'Database error message was ' . $exc->getMessage();
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return [];
        }
    }
    /**
     * @return int
     */
}
