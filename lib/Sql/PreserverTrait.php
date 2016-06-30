<?php
/**
 * Contains PreserverTrait Trait.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
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
     * @return \string[]
     * @throws \LogicException
     */
    public function getPreserveTos()
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
    public function preserveEveApi(EveApiEventInterface $event, $eventName, MediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $xml = $data->getEveApiXml();
        if (false === $xml) {
            return $event->setHandledSufficiently();
        }
        $this->getYem()
            ->triggerLogEvent(
                'Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__)
            );
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
                ->triggerLogEvent(
                    'Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]
                );
            $this->getPdo()
                ->rollBack();
            return $event;
        }
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getFinishedEventMessage($data, $eventName));
        return $event->setHandledSufficiently();
    }
    /**
     * @param \SimpleXMLElement[]|string $rows
     * @param array                      $columnDefaults
     * @param string                     $tableName
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function attributePreserveData($rows, array $columnDefaults, $tableName)
    {
        $maxRowCount = 1000;
        if (is_string($rows) || 0 === count($rows)) {
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
    protected function flush(array $columns, array $columnNames, $tableName)
    {
        if (0 === count($columns)) {
            return $this;
        }
        $rowCount = count($columns) / count($columnNames);
        $mess = sprintf('Have %1$s row(s) to upsert into %2$s table', $rowCount, $tableName);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $sql = $this->getCsq()
            ->getUpsert($tableName, $columnNames, $rowCount);
        $mess = preg_replace('/(,\(\?(?:,\?)*\))+/', ',...', $sql);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $mess = implode(',', $columns);
        if (512 < strlen($mess)) {
            $mess = substr($mess, 0, 512) . '...';
        }
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $this->getPdo()
            ->prepare($sql)
            ->execute($columns);
        return $this;
    }
    /**
     * @param array               $columnDefaults
     * @param \SimpleXMLElement[] $rows
     *
     * @return array
     */
    protected function processXmlRows(array $columnDefaults, array $rows)
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
    protected function valuesPreserveData(array $elements, array $columnDefaults, $tableName)
    {
        if (false === $elements || 0 === count($elements)) {
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
        $required = array_reduce($columnDefaults, function ($carry, $item) {
            return $carry + (int)(null === $item);
        }, 0);
        if ($required > $eleCount) {
            return $this;
        }
        uksort($columnDefaults, function ($alpha, $beta) {
            $alpha = strtolower($alpha);
            $beta = strtolower($beta);
            if ($alpha < $beta) {
                return -1;
            } elseif ($alpha > $beta) {
                return 1;
            }
            return 0;
        });
        return $this->flush(array_values($columnDefaults), array_keys($columnDefaults), $tableName);
    }
    /**
     * @var string[] preserveTos
     */
    protected $preserveTos = [];
}
