<?php
declare(strict_types = 1);
/**
 * LogEventInterface.php
 *
 * PHP version 7.0+
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\EventInterface;

/**
 * Interface LogEventInterface
 */
interface LogEventInterface extends EventInterface
{
    /**
     * @return array
     */
    public function getContext(): array;
    /**
     * @return int
     */
    public function getLevel(): int;
    /**
     * @return string
     */
    public function getMessage(): string;
    /**
     * @param array $value
     *
     * @return LogEventInterface Fluent interface.
     */
    public function setContext(array $value): LogEventInterface;
    /**
     * @param int $value
     *
     * @return LogEventInterface Fluent interface.
     */
    public function setLevel(int $value): LogEventInterface;
    /**
     * @param string $value
     *
     * @return LogEventInterface Fluent interface.
     */
    public function setMessage(string $value): LogEventInterface;
}
