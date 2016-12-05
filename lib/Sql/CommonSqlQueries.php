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

use Yapeal\Container\ContainerInterface;
use Yapeal\Container\DicAwareInterface;
use Yapeal\Container\DicAwareTrait;
use Yapeal\FileSystem\SafeFileHandlingTrait;

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
 * @method string getCachedUntilExpires($accountKey, $apiName, $ownerID)
 * @method string getDeleteFromStarbaseDetailTables($tableName, $ownerID, $starbaseID)
 * @method string getDeleteFromTable($tableName)
 * @method string getDeleteFromTableWithKeyID($tableName, $keyID)
 * @method string getDeleteFromTableWithOwnerID($tableName, $ownerID)
 * @method string getInitialization()
 * @method string getInsert(string $tableName, array $columnNameList, int $rowCount)
 * @method string getLatestYapealSchemaVersion()
 * @method string getMemberCorporationIDsExcludingAccountCorporations()
 * @method string getSchemaNames()
 * @method string getSelect(string $tableName, array $columnNameList, array $where)
 * @method string getUpsert($tableName, $columnNameList, $rowCount)
 */
class CommonSqlQueries implements DicAwareInterface
{
    use DicAwareTrait;
    use SafeFileHandlingTrait;
    use SqlSubsTrait;
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
        $this->platform = $dic['Yapeal.Sql.platform'];
        $this->queriesDir = $dic['Yapeal.Sql.dir'] . 'Queries/';
    }
    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \BadMethodCallException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __call(string $name, array $arguments = [])
    {
        $methodName = $name . ucfirst($this->platform);
        if (method_exists($this, $methodName)) {
            if (false !== $sql = call_user_func_array([$this, $methodName], $arguments)) {
                return $this->processSql($methodName, $sql, $arguments);
            }
        }
        if (false !== $result = $this->tryGet($name, $arguments)) {
            return $result;
        }
        $mess = 'Unknown method ' . $name;
        throw new \BadMethodCallException($mess);
    }
    /**
     * @param string $tableName
     * @param array  $columnNameList
     * @param int    $rowCount
     *
     * @return string
     * @throws \LogicException
     */
    protected function getInsertMysql(string $tableName, array $columnNameList, int $rowCount): string
    {
        $replacements = $this->getReplacements();
        $replacements['{tableName}'] = $tableName;
        $replacements['{columnNames}'] = implode('","', $columnNameList);
        $rowPrototype = '(' . implode(',', array_fill(0, count($columnNameList), '?')) . ')';
        $replacements['{rowset}'] = implode(',', array_fill(0, $rowCount, $rowPrototype));
        $sql = /** @lang text */
            'INSERT INTO "{schema}"."{tablePrefix}{tableName}" ("{columnNames}") VALUES {rowset}';
        return (string)str_replace(array_keys($replacements), array_values($replacements), $sql);
    }
    /**
     * @param string $tableName
     * @param array  $columnNameList
     * @param array  $where
     *
     * @return string
     * @throws \LogicException
     */
    protected function getSelectMysql(string $tableName, array $columnNameList, array $where): string
    {
        $replacements = $this->getReplacements();
        $replacements['{tableName}'] = $tableName;
        $replacements['{columnNames}'] = implode('","', $columnNameList);
        $wheres = [];
        foreach ($where as $key => $value) {
            $wheres[] = sprintf('"%s"=\'%s\'', $key, $value);
        }
        $replacements['{where}'] = implode(' AND ', $wheres);
        $sql = /** @lang text */
            'SELECT "{columnNames}" FROM "{schema}"."{tablePrefix}{tableName}" WHERE {where}';
        return (string)str_replace(array_keys($replacements), array_values($replacements), $sql);
    }
    /**
     * Returns a MySql version of an upsert query.
     *
     * @param string   $tableName
     * @param string[] $columnNameList
     * @param int      $rowCount
     *
     * @return string
     * @throws \LogicException
     */
    protected function getUpsertMysql(string $tableName, array $columnNameList, int $rowCount): string
    {
        $replacements = $this->getReplacements();
        $sql = $this->getInsertMysql($tableName, $columnNameList, $rowCount);
        $sql .= ' ON DUPLICATE KEY UPDATE {updates}';
        $updates = [];
        foreach ($columnNameList as $column) {
            $updates[] = sprintf('"%1$s"=VALUES("%1$s")', $column);
        }
        $replacements['{updates}'] = implode(',', $updates);
        return (string)str_replace(array_keys($replacements), array_values($replacements), $sql);
    }
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
    private function getReplacements(): array
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
     * @param string $fileName
     *
     * @param string $sql
     * @param array  $arguments
     *
     * @return string
     * @throws \LogicException
     */
    private function processSql(string $fileName, string $sql, array $arguments): string
    {
        $sql = $this->getCleanedUpSql($sql, $this->getReplacements());
        if (0 !== count($arguments)) {
            $sql = vsprintf($sql, $arguments);
        } else {
            $this->cacheSqlQuery($fileName, $sql);
        }
        return $sql;
    }
    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string|false
     * @throws \LogicException
     */
    private function tryGet(string $name, array $arguments = [])
    {
        if (0 === strpos($name, 'get')) {
            $fileNames = explode(',',
                sprintf('%1$s%2$s.%3$s.sql,%1$s%2$s.sql', $this->queriesDir, $name, $this->platform));
            foreach ($fileNames as $fileName) {
                if ($this->isCachedSql($fileName)) {
                    return $this->getCachedSql($fileName);
                }
                if (false === $sql = $this->safeFileRead($fileName)) {
                    continue;
                }
                return $this->processSql($fileName, $sql, $arguments);
            }
        }
        return false;
    }
    /**
     * @var string $platform
     */
    private $platform;
    /**
     * @var string $queriesDir
     */
    private $queriesDir;
    /**
     * @var array $replacements Holds a list of Sql section replacement pairs.
     */
    private $replacements;
    /**
     * @var array sqlCache
     */
    private $sqlCache = [];
}
