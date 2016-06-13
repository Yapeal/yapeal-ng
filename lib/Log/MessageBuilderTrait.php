<?php
/**
 * Contains MessageBuilderTrait Trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait MessageBuilderTrait
 */
trait MessageBuilderTrait
{
    /**
     * @param string                   $messagePrefix
     * @param EveApiReadWriteInterface $data
     *
     * @return string
     * @throws \LogicException
     */
    protected function createEveApiMessage($messagePrefix, EveApiReadWriteInterface $data)
    {
        $mess = $messagePrefix . ' Eve API %1$s/%2$s';
        $subs = [lcfirst($data->getEveApiSectionName()), $data->getEveApiName()];
        if ($data->hasEveApiArgument('keyID')) {
            $mess .= ' for keyID = %3$s';
            $subs[] = $data->getEveApiArgument('keyID');
            if ($data->hasEveApiArgument('characterID')) {
                $mess .= ' and characterID = %4$s';
                $subs[] = $data->getEveApiArgument('characterID');
            } elseif ($data->hasEveApiArgument('corporationID')) {
                $mess .= ' and corporationID = %4$s';
                $subs[] = $data->getEveApiArgument('corporationID');
            }
        }
        return vsprintf($mess, $subs);
    }
    /**
     * @param string                   $messagePrefix
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function createEventMessage($messagePrefix, EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix .= sprintf(' %s event from', $eventName);
        return $this->createEveApiMessage($messagePrefix, $data);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getEmittingEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Emitting';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getEmptyXmlDataMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'XML empty after';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     * @param string                   $fileName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getFailedToWriteFile(EveApiReadWriteInterface $data, $eventName, $fileName)
    {
        $messagePrefix = sprintf('Failed writing %s file during', $fileName);
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getFinishedEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Finished';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getNonHandledEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Nothing reported handling';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     * @param string                   $location
     *
     * @return string
     * @throws \LogicException
     */
    protected function getReceivedEventMessage(EveApiReadWriteInterface $data, $eventName, $location)
    {
        $messagePrefix = sprintf('Received in %s', $location);
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getSufficientlyHandledEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Sufficiently handled';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventName
     *
     * @return string
     * @throws \LogicException
     */
    protected function getWasHandledEventMessage(EveApiReadWriteInterface $data, $eventName)
    {
        $messagePrefix = 'Was handled';
        return $this->createEventMessage($messagePrefix, $data, $eventName);
    }
}
