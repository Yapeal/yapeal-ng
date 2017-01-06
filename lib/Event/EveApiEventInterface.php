<?php
declare(strict_types = 1);
/**
 * Contains EveApiEventInterface Interface.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2017 Michael Cummings
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
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\EventInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Interface EveApiEventInterface
 */
interface EveApiEventInterface extends EventInterface
{
    /**
     * Get data object.
     *
     * @return EveApiReadWriteInterface
     * @throws \LogicException Throws exception if code tries to access data before it is set.
     */
    public function getData(): EveApiReadWriteInterface;
    /**
     * Used to check if event was handled sufficiently by any listener(s).
     *
     * This should return true when a listener uses setHandledSufficiently() and/or eventHandled() methods for the
     * event.
     *
     * @return bool
     */
    public function isSufficientlyHandled(): bool;
    /**
     * Set data object.
     *
     * @param EveApiReadWriteInterface $value
     *
     * @return static Fluent interface.
     */
    public function setData(EveApiReadWriteInterface $value);
    /**
     * Set to indicate event was handled sufficiently while still allows additional listener(s) to have a chance to
     * handle the event as well.
     *
     * @param bool $value
     *
     * @return static Fluent interface.
     */
    public function setHandledSufficiently(bool $value = true);
}
