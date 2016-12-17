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
     * @throws \UnexpectedValueException
     */
    public function preserveEveApi(
        EveApiEventInterface $event,
        string $eventName,
        MediatorInterface $yem
    ): EveApiEventInterface {
        if (!$this->shouldPreserve()) {
            return $event;
        }
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent('Yapeal.Log.log',
                Logger::DEBUG,
                $this->getReceivedEventMessage($data, $eventName, __CLASS__));
        if ('' === $data->getEveApiXml()) {
            return $event;
        }
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
            $yem->triggerLogEvent('Yapeal.Log.log',
                    Logger::WARNING,
                    $this->createEveApiMessage($mess, $data),
                    ['exception' => $exc]);
            $this->getPdo()
                ->rollBack();
            return $event;
        }
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getFinishedEventMessage($data, $eventName));
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
        $this->preserve = $value;
        return $this;
    }
    /**
     * Used to process the most common attribute rowset style of API data.
     *
     * Most Eve APIs use a set of rowset tags containing row tags. Some of them nest additional rowsets inside of the
     * rows like with the AssetList APIs where contents of hangers, ships, and other containers are done this way. A few
     * of the APIs are made up of a collection of rowset elements instead. The top level rowset tags have columns, key,
     * and name attributes. Each row tag inside of the rowset will have attributes with the same names as listed in the
     * columns attribute from the rowset. Depending on the API some of the row attributes may be missing and have known
     * default values that are used instead or are considered optional in the database table and can be NULL.
     *
     * @param \SimpleXMLElement[] $rows
     * @param array               $columnDefaults
     * @param string              $tableName
     *
     * @return static Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function attributePreserveData(array $rows, array $columnDefaults, string $tableName)
    {
        $this->lastColumnCount = 0;
        $this->lastRowCount = 0;
        unset($this->pdoStatement);
        if (0 === $rowCount = count($rows)) {
            return $this;
        }
        $columnNames = array_keys($columnDefaults);
        /**
         * Determines the maximum number of rows per SQL query.
         *
         * ## Background
         *
         * Coming up with a good chunk size is harder than it seems. First there a lot of Eve APIs with just few rows
         * like Account APIKeyInfo or Corp AccountBalance then there are others like Eve AllianceList, or Corp AssetList
         * which have 1000s or maybe even 10000s of rows for the last one in some larger corps with a lot of offices.
         *
         * On the SQL side of things larger queries are generally more efficient but also take up a lot more memory to
         * build. Plus very large queries tend to exceed limits built into the driver or database server itself to
         * protect against DOS attacks etc.
         *
         * After a lot of feedback from application developers and issues reports the upper limit seems to be around
         * 1000 rows at least with MySQL which has been the only test platform used in the past with Yapeal-ng. The
         * other factor is the OS the database is running on. The Windows drivers at least for MySQL seem to cause the
         * most issues but as stated 1000 rows seems to keep the problems from turn up. There are some php.ini settings
         * that can be changed to help with using larger queries but not everyone has access to them depending on where
         * they're host their site and other reasons.
         *
         * So to summarize for the really large Eve APIs results you want to use as few large queries as you can without
         * exceeding database platform or OS limits while also not needlessly breaking up smaller results which would
         * hurt performance and efficiency.
         *
         * ## Explaining the code
         *
         *   1. Take the row count and divide it by 4 throwing away any remainder to help keep memory use down without
         *   create tons of queries to process which is less efficient.
         *   2. Make sure for larger Eve APIs not to exceed 1000 rows chunks using min().
         *   3. Insure small and medium size Eve APIs aren't broken up needlessly by enforcing minimum of 100 rows
         *   chunks by using max().
         *
         * @var int $chunkSize
         */
        $chunkSize = max(100, min(1000, intdiv($rowCount, 4)));
        for ($pos = 0; $pos <= $rowCount; $pos += $chunkSize) {
            $this->flush($this->processXmlRows(array_slice($rows, $pos, $chunkSize, false), $columnDefaults),
                $columnNames,
                $tableName);
        }
        return $this;
    }
    /**
     * Used by all styles of Eve APIs to prepare and execute their SQL 'upsert' queries.
     *
     * 'Upsert' is a commonly used term for updating any existing rows in a table and inserting all the ones that don't
     * already exist together at one time.
     *
     * The method also tracks if the prepared query can be re-used or not to take fuller advantage of them in cases
     * where all queries have the same number of database rows as is common with some of the larger APIs and a few that
     * always have a fixed number of rows.
     *
     * @param string[] $columns
     * @param string[] $columnNames
     * @param string   $tableName
     *
     * @return static Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function flush(array $columns, array $columnNames, string $tableName)
    {
        if (0 === count($columns)) {
            return $this;
        }
        $rowCount = intdiv(count($columns), count($columnNames));
        $mess = sprintf('Have %s row(s) to upsert into %s table', $rowCount, $tableName);
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $isNotPrepared = $this->lastColumnCount !== count($columnNames)
            || $this->lastRowCount !== $rowCount
            || null === $this->pdoStatement;
        if ($isNotPrepared) {
            $sql = $this->getCsq()
                ->getUpsert($tableName, $columnNames, $rowCount);
            $mess = preg_replace('%(,\([?,]*\))+%', ',...', $sql);
            if (PREG_NO_ERROR !== $lastError = preg_last_error()) {
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
        $mess = '';
        foreach ($columns as $column) {
            $mess .= $column . ',';
            if (256 <= strlen($mess)) {
                break;
            }
        }
        $mess = substr($mess, 0, 256) . '...';
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $this->pdoStatement->execute($columns);
        return $this;
    }
    /**
     * Combines the column defaults with a set of rows.
     *
     * @param \SimpleXMLElement[] $rows
     *
     * @param array               $columnDefaults
     *
     * @return array
     */
    protected function processXmlRows(array $rows, array $columnDefaults): array
    {
        $callback = function (array $carry, \SimpleXMLElement $row) use ($columnDefaults): array {
            foreach ($columnDefaults as $key => $value) {
                $attribute = (string)$row[$key];
                $carry[] = '' !== $attribute ? $attribute : (string)$value;
            }
            return $carry;
        };
        return array_reduce($rows, $callback, []);
    }
    /**
     * Used to process the second most common style of API data.
     *
     * Transforms a list of XML tags and their values into column names and values. $columnDefaults is used to both set
     * default values for required columns and to act as a set of known column names.
     *
     * @param \SimpleXMLElement[] $elements
     * @param array               $columnDefaults
     * @param string              $tableName
     *
     * @return static Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function valuesPreserveData(array $elements, array $columnDefaults, string $tableName)
    {
        if (0 === count($elements)) {
            return $this;
        }
        $defaultNames = array_keys($columnDefaults);
        $callback = function (array $carry, \SimpleXMLElement $element) use ($defaultNames): array {
            if (in_array($name = $element->getName(), $defaultNames, true)) {
                $carry[$name] = (string)$element;
            }
            return $carry;
        };
        /*
         * The array reduce returns only elements with names in $columnDefaults. It also converts them from
         * SimpleXMLElements to a plain associative array.
         * Array replace is used to overwrite the column default values with any values given in the filtered and
         * converted elements. This also assures they are in the correct order.
         */
        $columns = array_replace($columnDefaults, array_reduce($elements, $callback, []));
        return $this->flush(array_values($columns), $defaultNames, $tableName);
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
     * @var int $lastColumnCount
     */
    private $lastColumnCount;
    /**
     * @var int lastRowCount
     */
    private $lastRowCount;
    /**
     * @var \PDOStatement $pdoStatement
     */
    private $pdoStatement;
    /**
     * @var bool $preserve
     */
    private $preserve = true;
}
