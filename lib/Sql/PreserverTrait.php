<?php
declare(strict_types = 1);
/**
 * Contains PreserverTrait Trait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Sql;

use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Trait PreserverTrait
 *
 * @method CommonSqlQueries getCsq()
 * @method \PDO getPdo()
 * @method MediatorInterface getYem()
 */
trait PreserverTrait
{
    /**
     * @return string[]
     * @throws \LogicException
     */
    public function getPreserveTos(): array
    {
        if (0 === count($this->preserveTos)) {
            $mess = 'Tried to access preserveTos before it was set';
            throw new \LogicException($mess);
        }
        return $this->preserveTos;
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
    public function preserveEveApi(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface
    {
        if (!$this->shouldPreserve()) {
            return $event;
        }
        $this->setYem($yem);
        $data = $event->getData();
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        $this->getPdo()
            ->beginTransaction();
        try {
            foreach ($this->getPreserveTos() as $preserveTo) {
                $this->$preserveTo($data);
            }
            $this->getPdo()
                ->commit();
        } catch (\PDOException $exc) {
            $mess = 'Failed to upsert data of';
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            $this->getPdo()
                ->rollBack();
            return $event;
        }
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getFinishedEventMessage($data, $eventName));
        return $event->setHandledSufficiently();
    }
    /**
     * Turn on or off preserving of Eve API data by this preserver.
     *
     * Allows class to stay registered for events but be enabled or disabled during runtime.
     *
     * @param boolean $value
     *
     * @return $this Fluent interface
     */
    public function setPreserve(bool $value = true)
    {
        $this->preserve = (boolean)$value;
        return $this;
    }
    /**
     * @param \SimpleXMLElement[] $rows
     * @param array               $columnDefaults
     * @param string              $tableName
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function attributePreserveData(array $rows, array $columnDefaults, string $tableName)
    {
        $maxRowCount = 1000;
        $this->lastColumnCount = 0;
        $this->lastRowCount = 0;
        unset($this->pdoStatement);
        if (0 === count($rows)) {
            return $this;
        }
        $rows = array_chunk($rows, $maxRowCount, true);
        $columnNames = array_keys($columnDefaults);
        foreach ($rows as $chunk) {
            $this->flush($this->processXmlRows($columnDefaults, $chunk), $columnNames, $tableName);
        }
        return $this;
    }
    /**
     * @param string[] $columns
     * @param string[] $columnNames
     * @param string   $tableName
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function flush(array $columns, array $columnNames, string $tableName)
    {
        if (0 === count($columns)) {
            return $this;
        }
        $rowCount = intdiv(count($columns), count($columnNames));
        $mess = sprintf('Have %1$s row(s) to upsert into %2$s table', $rowCount, $tableName);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $isNotPrepared = $this->lastColumnCount !== count($columnNames)
            || $this->lastRowCount !== $rowCount
            || null === $this->pdoStatement;
        if ($isNotPrepared) {
            $sql = $this->getCsq()
                ->getUpsert($tableName, $columnNames, $rowCount);
            $mess = preg_replace('%(,\([?,]*\))+%', ',...', $sql);
            $lastError = preg_last_error();
            if (PREG_NO_ERROR !== $lastError) {
                $constants = array_flip(get_defined_constants(true)['pcre']);
                $lastError = $constants[$lastError];
                $mess = 'Received preg error ' . $lastError;
                throw new \DomainException($mess);
            }
            $this->getYem()
                ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
            $this->pdoStatement = $this->getPdo()
                ->prepare($sql);
            $this->lastColumnCount = count($columnNames);
            $this->lastRowCount = $rowCount;
        }
        $mess = substr(implode(',', $columns), 0, 256);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $this->pdoStatement->execute($columns);
        return $this;
    }
    /**
     * @param array               $columnDefaults
     * @param \SimpleXMLElement[] $rows
     *
     * @return array
     */
    protected function processXmlRows(array $columnDefaults, array $rows): array
    {
        $columns = [];
        foreach ($rows as $row) {
            // Replace empty values with any existing defaults.
            foreach ($columnDefaults as $key => $value) {
                if (null === $value || '' !== (string)$row[$key]) {
                    $columns[] = (string)$row[$key];
                    continue;
                }
                $columns[] = (string)$value;
            }
        }
        return $columns;
    }
    /**
     * @param \SimpleXMLElement[] $elements
     * @param array               $columnDefaults
     * @param string              $tableName
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function valuesPreserveData(array $elements, array $columnDefaults, string $tableName)
    {
        if (0 === count($elements)) {
            return $this;
        }
        $eleCount = 0;
        foreach ($elements as $element) {
            $columnName = $element->getName();
            if (!array_key_exists($columnName, $columnDefaults)) {
                continue;
            }
            ++$eleCount;
            if ('' !== (string)$element || null === $columnDefaults[$columnName]) {
                $columnDefaults[$columnName] = (string)$element;
            }
        }
        $required = array_reduce($columnDefaults,
            function ($carry, $item) {
                return $carry + (int)(null === $item);
            },
            0);
        if ($required > $eleCount) {
            return $this;
        }
        uksort($columnDefaults,
            function ($alpha, $beta) {
                return strtolower($alpha) <=> strtolower($beta);
            });
        return $this->flush(array_values($columnDefaults), array_keys($columnDefaults), $tableName);
    }
    /**
     * @var string[] preserveTos
     */
    protected $preserveTos = [];
    /**
     * @return bool
     */
    private function shouldPreserve(): bool
    {
        return $this->preserve;
    }
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * @var int $lastColumnCount
     */
    private $lastColumnCount;
    /**
     * @var int lastRowCount
     */
    private $lastRowCount;
    /**
     * @var bool $preserve
     */
    private $preserve = true;
}
