<?php
declare(strict_types = 1);
/**
 * Contains EveApiReadWriteInterface Interface.
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
namespace Yapeal\Xml;

/**
 * Interface EveApiReadWriteInterface
 */
interface EveApiReadWriteInterface
{
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param string $value
     *
     * @return static Fluent interface.
     */
    public function addEveApiArgument(string $name, string $value);
    /**
     * Getter for cache interval.
     *
     * @return int
     * @throws \LogicException
     */
    public function getCacheInterval(): int;
    /**
     * Getter for an existing Eve API argument.
     *
     * @param string $name
     *
     * @return string
     * @throws \DomainException Throws exception for unknown arguments.
     */
    public function getEveApiArgument(string $name): string;
    /**
     * Getter for Eve API argument list.
     *
     * @return string[]
     */
    public function getEveApiArguments(): array;
    /**
     * Getter for name of Eve API.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiName(): string;
    /**
     * Getter for name of Eve API section.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiSectionName(): string;
    /**
     * Getter for the actual Eve API XML received.
     *
     * @return string
     */
    public function getEveApiXml(): string;
    /**
     * Used to get a repeatable unique hash for any combination API name, section, and arguments.
     *
     * @return string
     */
    public function getHash(): string;
    /**
     * Used to check if an argument exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEveApiArgument(string $name): bool;
    /**
     * Cache interval setter.
     *
     * @param int $value Caching interval in seconds.
     *
     * @return static Fluent interface.
     */
    public function setCacheInterval(int $value);
    /**
     * Used to set a list of arguments used when forming request to Eve Api
     * server.
     *
     * Things like KeyID, vCode etc that are either required or optional for the
     * Eve API. See adder for example.
     *
     * Example:
     * <code>
     * <?php
     * $args = array( 'KeyID' => '1156', 'vCode' => 'abc123');
     * $api->setEveApiArguments($args);
     * ...
     * </code>
     *
     * @param string[] $values
     *
     * @return static Fluent interface.
     * @uses EveApiXmlData::addEveApiArgument()
     */
    public function setEveApiArguments(array $values);
    /**
     * Eve API name setter.
     *
     * @param string $value
     *
     * @return static Fluent interface.
     */
    public function setEveApiName(string $value);
    /**
     * Eve API section name setter.
     *
     * @param string $value
     *
     * @return static Fluent interface.
     */
    public function setEveApiSectionName(string $value);
    /**
     * Sets the actual Eve API XML data received.
     *
     * @param string $xml XML data.
     *
     * @return static Fluent interface.
     */
    public function setEveApiXml(string $xml = '');
}
