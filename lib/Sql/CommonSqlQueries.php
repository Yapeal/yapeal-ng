<?php
declare(strict_types = 1);
/**
 * Contains CommonSqlQueries class.
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

use Yapeal\DicAwareInterface;
use Yapeal\DicAwareTrait;
use Yapeal\Event\YEMAwareInterface;
use Yapeal\Event\YEMAwareTrait;
use Yapeal\FileSystem\CommonFileHandlingTrait;

/**
 * Class CommonSqlQueries
 *
 * @method string getAccountCorporationIDsExcludingCorporationKeys()
 * @method string getActiveApis()
 * @method string getActiveMailBodiesWithOwnerID($ownerID)
 * @method string getActiveRegisteredAccountStatus($mask)
 * @method string getActiveRegisteredCharacters($mask)
 * @method string getActiveRegisteredCorporations($mask)
 * @method string getActiveRegisteredKeys()
 * @method string getActiveStarbaseTowers($mask, $ownerID)
 * @method string getApiLock($hash)
 * @method string getApiLockRelease($hash)
 * @method string getCreateAddOrModifyColumnProcedure()
 * @method string getDeleteFromTable($tableName)
 * @method string getDeleteFromTableWithKeyID($tableName, $keyID)
 * @method string getDeleteFromTableWithOwnerID($tableName, $ownerID)
 * @method string getDropAddOrModifyColumnProcedure()
 * @method string getMemberCorporationIDsExcludingAccountCorporations()
 * @method string getUtilLatestDatabaseVersion()
 * @method string initialization()
 */
class CommonSqlQueries implements DicAwareInterface, YEMAwareInterface
{
    use CommonFileHandlingTrait, DicAwareTrait, SqlSubsTrait, YEMAwareTrait;
    /**
     * @param string $databaseName
     * @param string $tablePrefix
     */
    public function __construct($databaseName, $tablePrefix)
    {
        $this->databaseName = $databaseName;
        $this->tablePrefix = $tablePrefix;
    }
    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws \BadMethodCallException
     * @throws \LogicException
     */
    public function __call(string $name, array $arguments = [])
    {
        $fileNames = explode(',',
            sprintf('%1$s%2$s.%3$s.sql,%1$s%2$s.sql',
                $this->getDic()['Yapeal.Sql.dir'] . 'queries/',
                $name,
                $this->getDic()['Yapeal.Sql.platform']));
        foreach ($fileNames as $fileName) {
            if ($this->isCachedSql($fileName)) {
                return $this->getCachedSql($fileName);
            }
            if (!is_readable($fileName) || !is_file($fileName)) {
                continue;
            }
            $sql = $this->safeFileRead($fileName);
            if (false === $sql) {
                continue;
            }
            return $this->processSql($arguments, $sql, $fileName);
        }
        $mess = 'Unknown method ' . $name;
        throw new \BadMethodCallException($mess);
    }
    /**
     * @param string $tableName
     * @param array  $columnNameList
     * @param string $rowCount
     *
     * @return string
     */
    public function getUpsert($tableName, array $columnNameList, $rowCount)
    {
        $columns = implode('","', $columnNameList);
        $rowPrototype = '(' . implode(',', array_fill(0, count($columnNameList), '?')) . ')';
        $rows = implode(',', array_fill(0, $rowCount, $rowPrototype));
        $updates = [];
        foreach ($columnNameList as $column) {
            $updates[] = '"' . $column . '"=VALUES("' . $column . '")';
        }
        $updates = implode(',', $updates);
        $sql = sprintf('INSERT INTO "%1$s"."%2$s%3$s" ("%4$s") VALUES %5$s ON DUPLICATE KEY UPDATE %6$s',
            $this->databaseName,
            $this->tablePrefix,
            $tableName,
            $columns,
            $rows,
            $updates);
        return $sql;
    }
    /**
     * @param array <string, string> $columns
     *
     * @return array<string, string>
     */
    public function getUtilCachedUntilExpires(array $columns)
    {
        $where = [];
        // I.E. "apiName" = 'accountBalance'
        foreach ($columns as $key => $value) {
            $where[] = sprintf('"%1$s" = \'%2$s\'', $key, $value);
        }
        $where = implode(' AND ', $where);
        /** @lang MySQL */
        $sql = <<<'SQL'
SELECT "expires"
 FROM "%1$s"."%2$sutilCachedUntil"
 WHERE %3$s
SQL;
        return sprintf(str_replace(["\n", "\r\n"], '', $sql),
            $this->databaseName,
            $this->tablePrefix,
            $where);
    }
    /**
     * @return string
     */
    public function getUtilLatestDatabaseVersionUpdate()
    {
        return $this->getUpsert('utilDatabaseVersion', ['version'], 1);
    }
    /**
     * @var string $databaseName
     */
    protected $databaseName;
    /**
     * @var string $tablePrefix
     */
    protected $tablePrefix;
    /**
     * @param string $fileName
     * @param string $sql
     */
    private function cacheSqlQuery(string $fileName, string $sql)
    {
        $this->sqlCache[$fileName] = $sql;
    }
    /**
     * @param string $fileName
     *
     * @return string
     */
    private function getCachedSql(string $fileName): string
    {
        return $this->sqlCache[$fileName];
    }
    /**
     * @return array
     * @throws \LogicException
     */
    private function getReplacements()
    {
        if (null === $this->replacements) {
            $this->replacements = $this->getSqlSubs($this->getDic());
        }
        return $this->replacements;
    }
    /**
     * @param string $fileName
     *
     * @return bool
     */
    private function isCachedSql(string $fileName): bool
    {
        return array_key_exists($fileName, $this->sqlCache);
    }
    /**
     * @param array  $arguments
     * @param string $sql
     * @param string $fileName
     *
     * @return string
     * @throws \LogicException
     */
    private function processSql(array $arguments, string $sql, string $fileName)
    {
        $sql = str_replace(["\n ", "\r\n "], ' ', $sql);
        $replacements = $this->getReplacements();
        $sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
        if (0 !== count($arguments)) {
            $sql = vsprintf($sql, $arguments);
        } else {
            $this->cacheSqlQuery($fileName, $sql);
        }
        return $sql;
    }
    /**
     * @var array $replacements Holds a list of Sql section replacement pairs.
     */
    private $replacements;
    /**
     * @var array sqlCache
     */
    private $sqlCache = [];
}
