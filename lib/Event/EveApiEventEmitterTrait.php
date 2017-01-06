<?php
declare(strict_types = 1);
/**
 * Contains EveApiEventEmitterTrait Trait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2017 Michael Cummings
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
 * @copyright 2015-2017 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use Yapeal\Log\Logger;
use Yapeal\Log\MessageBuilderTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait EveApiEventEmitterTrait
 */
trait EveApiEventEmitterTrait
{
    use MessageBuilderTrait, YEMAwareTrait;
    /**
     * Emits a series of Eve API events and logs the handling of them.
     *
     * Events are emitted (triggered) from the most specific 'Prefix.Section.Api.Suffix' through to the least specific
     * 'Prefix.Suffix' until one of the events returns with hasBeenHandled() === true or there are no more events left
     * to emit.
     *
     * Log events are created for handled, sufficiently handled, and non-handled event.
     *
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventSuffix
     * @param string                   $eventPrefix
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function emitEvents(
        EveApiReadWriteInterface $data,
        string $eventSuffix,
        string $eventPrefix = 'Yapeal.EveApi'
    ): bool
    {
        $yem = $this->getYem();
        $eventNames = $this->getEmitterEvents($data, $eventSuffix, $eventPrefix);
        $event = null;
        /**
         * @var bool $sufficientlyHandled
         */
        $sufficientlyHandled = false;
        foreach ($eventNames as $eventName) {
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getEmittingEventMessage($data, $eventName));
            $event = $yem->triggerEveApiEvent($eventName, $data);
            $data = $event->getData();
            if ($event->hasBeenHandled()) {
                $yem->triggerLogEvent('Yapeal.Log.log',
                    Logger::INFO,
                    $this->getWasHandledEventMessage($data, $eventName));
                $sufficientlyHandled = true;
                break;
            }
            if ($event->isSufficientlyHandled()) {
                $yem->triggerLogEvent('Yapeal.Log.log',
                    Logger::INFO,
                    $this->getSufficientlyHandledEventMessage($data, $eventName));
                $sufficientlyHandled = true;
            }
        }
        if (null === $event || false === $sufficientlyHandled) {
            $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::NOTICE,
                $this->getNonHandledEventMessage($data, $eventSuffix));
            return false;
        }
        return true;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventSuffix
     * @param string                   $eventPrefix
     *
     * @return array|string[]
     * @throws \LogicException
     */
    private function getEmitterEvents(EveApiReadWriteInterface $data, string $eventSuffix, string $eventPrefix): array
    {
        // Prefix.Section.Api.Suffix, Prefix.Api.Suffix,
        // Prefix.Section.Suffix, Prefix.Suffix
        /**
         * @var string[] $eventNames
         */
        $eventNames = explode(',',
            sprintf('%3$s.%1$s.%2$s.%4$s,%3$s.%2$s.%4$s,%3$s.%1$s.%4$s,%3$s.%4$s',
                ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                $eventPrefix,
                $eventSuffix));
        return $eventNames;
    }
}
