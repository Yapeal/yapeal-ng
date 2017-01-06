<?php
declare(strict_types = 1);
/**
 * Contains EveApiXmlData class.
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
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xml;

/**
 * Class EveApiXmlData
 */
class EveApiXmlData implements EveApiReadWriteInterface
{
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function addEveApiArgument(string $name, string $value): self
    {
        $this->eveApiArguments[$name] = $value;
        return $this;
    }
    /**
     * Getter for cache interval.
     *
     * @return int
     * @throws \LogicException
     */
    public function getCacheInterval(): int
    {
        return $this->cacheInterval;
    }
    /**
     * Getter for an existing Eve API argument.
     *
     * @param string $name
     *
     * @return string
     * @throws \DomainException
     */
    public function getEveApiArgument(string $name): string
    {
        if (!array_key_exists($name, $this->eveApiArguments)) {
            $mess = 'Unknown argument ' . $name;
            throw new \DomainException($mess);
        }
        return $this->eveApiArguments[$name];
    }
    /**
     * Getter for Eve API argument list.
     *
     * @return string[]
     */
    public function getEveApiArguments(): array
    {
        return $this->eveApiArguments;
    }
    /**
     * Getter for name of Eve API.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiName(): string
    {
        if (null === $this->eveApiName) {
            $mess = 'Tried to access Eve Api name before it was set';
            throw new \LogicException($mess);
        }
        return $this->eveApiName;
    }
    /**
     * Getter for name of Eve API section.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiSectionName(): string
    {
        if (null === $this->eveApiSectionName) {
            $mess = 'Tried to access Eve Api section name before it was set';
            throw new \LogicException($mess);
        }
        return $this->eveApiSectionName;
    }
    /**
     * Getter for the actual Eve API XML received.
     *
     * @return string
     */
    public function getEveApiXml(): string
    {
        return $this->eveApiXml;
    }
    /**
     * Used to get a repeatable unique hash for any combination API name, section, and arguments.
     *
     * @return string
     * @throws \LogicException
     */
    public function getHash(): string
    {
        $hash = $this->getEveApiName() . $this->getEveApiSectionName();
        $arguments = $this->getEveApiArguments();
        unset($arguments['mask'], $arguments['rowCount']);
        ksort($arguments);
        foreach ($arguments as $key => $value) {
            $hash .= $key . $value;
        }
        return hash('md5', $hash);
    }
    /**
     * Used to check if an argument exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEveApiArgument(string $name): bool
    {
        return array_key_exists($name, $this->eveApiArguments);
    }
    /**
     * Cache interval setter.
     *
     * @param int $value Caching interval in seconds.
     *
     * @return self Fluent interface.
     */
    public function setCacheInterval(int $value): self
    {
        $this->cacheInterval = (int)$value;
        return $this;
    }
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
     * @return self Fluent interface.
     * @uses EveApiXmlData::addEveApiArgument()
     */
    public function setEveApiArguments(array $values): self
    {
        $this->eveApiArguments = [];
        if (0 === count($values)) {
            return $this;
        }
        foreach ($values as $name => $value) {
            $this->addEveApiArgument($name, $value);
        }
        return $this;
    }
    /**
     * Eve API name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setEveApiName(string $value): self
    {
        $this->eveApiName = $value;
        return $this;
    }
    /**
     * Eve API section name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setEveApiSectionName(string $value): self
    {
        $this->eveApiSectionName = $value;
        return $this;
    }
    /**
     * Sets the actual Eve API XML data received.
     *
     * @param string $xml Actual XML content.
     *
     * @return self Fluent interface.
     */
    public function setEveApiXml(string $xml = ''): self
    {
        $this->eveApiXml = $xml;
        return $this;
    }
    /**
     * Holds expected/calculated cache interval for the current API in seconds.
     *
     * @var int $cacheInterval
     */
    private $cacheInterval = 300;
    /**
     * List of API arguments.
     *
     * @var string[] $eveApiArguments
     */
    private $eveApiArguments = [];
    /**
     * Holds Eve API name.
     *
     * @var string $eveApiName
     */
    private $eveApiName;
    /**
     * Holds Eve API section name.
     *
     * @var string $eveApiSectionName
     */
    private $eveApiSectionName;
    /**
     * Holds the actual Eve API XML data.
     *
     * @var string $eveApiXml
     */
    private $eveApiXml = '';
}
