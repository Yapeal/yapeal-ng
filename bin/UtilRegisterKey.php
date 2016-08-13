<?php
declare(strict_types = 1);
/**
 * Contains UtilRegisterKey class.
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
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
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
namespace Yapeal;

use Yapeal\Exception\YapealDatabaseException;

/**
 * Class UtilRegisterKey
 *
 * WARNING: This class changes the PDO connection into MySQL's ANSI,TRADITIONAL
 * mode and makes other changes that may cause other queries in any other code
 * that reuses the connection after the changes to fail. For example if you use
 * things like back-tick quotes in queries they may cause the query to fail or
 * issue warnings. You can find out more about MySQL modes at
 * {@link http://dev.mysql.com/doc/refman/5.5/en/sql-mode.html}
 */
class UtilRegisterKey
{
    /**
     * @param \PDO $pdo
     * @param string $databaseName
     * @param string $tablePrefix
     */
    public function __construct(\PDO $pdo, string $databaseName = 'yapeal-ng', string $tablePrefix = '')
    {
        $this->setPdo($pdo)
            ->setDatabaseName($databaseName)
            ->setTablePrefix($tablePrefix);
    }
    /**
     * @return bool
     * @throws \LogicException
     */
    public function getActive(): bool
    {
        return $this->isActive();
    }
    /**
     * @return string
     * @throws \LogicException
     */
    public function getActiveAPIMask(): string
    {
        if (null === $this->activeAPIMask) {
            $mess = 'Tried to access "activeAPIMask" before it was set';
            throw new \LogicException($mess);
        }
        return $this->activeAPIMask;
    }
    /**
     * @return string
     * @throws \LogicException
     */
    public function getKeyID(): string
    {
        if (null === $this->keyID) {
            $mess = 'Tried to access "keyID" before it was set';
            throw new \LogicException($mess);
        }
        return $this->keyID;
    }
    /**
     * @return string
     * @throws \LogicException
     */
    public function getVCode(): string
    {
        if (null === $this->vCode) {
            $mess = 'Tried to access "vCode" before it was set';
            throw new \LogicException($mess);
        }
        return $this->vCode;
    }
    /**
     * @return bool
     * @throws \LogicException
     */
    public function isActive(): bool
    {
        if (null === $this->active) {
            $mess = 'Tried to access "active" before it was set';
            throw new \LogicException($mess);
        }
        return $this->active;
    }
    /**
     * Used to load an existing RegisteredKey row from database.
     *
     * @return UtilRegisterKey Fluent interface.
     * @throws \LogicException
     * @throws YapealDatabaseException
     */
    public function load(): self
    {
        $stmt = $this->initPdo()
            ->getPdo()
            ->query($this->getExistingRegisteredKeyById());
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (1 !== count($result)) {
            $mess = sprintf('Expect to receive a single row for "%1$s" but got %2$s',
                $this->getKeyID(),
                count($result));
            throw new YapealDatabaseException($mess);
        }
        foreach ($this->getColumnNames() as $column) {
            /** @noinspection PhpVariableVariableInspection */
            $this->$column = $result[0][$column];
        }
        return $this;
    }
    /**
     * Method used to persist changes to the database.
     *
     * NOTE: After calling this method the MySQL PDO connection will be
     * switched to ANSI mode and use UTF-8.
     *
     * @see UtilRegisteredKey
     * @return UtilRegisterKey Fluent interface.
     * @throws \LogicException
     */
    public function save(): UtilRegisterKey
    {
        $stmt = $this->initPdo()
            ->getPdo()
            ->prepare($this->getUpsert());
        $columns = [
            $this->getActive(),
            $this->getActiveAPIMask(),
            $this->getKeyID(),
            $this->getVCode()
        ];
        $stmt->execute($columns);
        return $this;
    }
    /**
     * @param bool $value
     *
     * @return UtilRegisterKey Fluent interface.
     */
    public function setActive($value = true): self
    {
        $this->active = (bool)$value;
        return $this;
    }
    /**
     * @param string|int $value
     *
     * @return UtilRegisterKey Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setActiveAPIMask($value): self
    {
        if (is_int($value)) {
            $value = (string)$value;
        }
        if (!is_string($value)) {
            $mess = 'ActiveAPIMask MUST be an integer or integer string but was given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        if (!$this->isIntString($value)) {
            $mess = 'ActiveAPIMask MUST be an integer or integer string but was given ' . $value;
            throw new \InvalidArgumentException($mess);
        }
        $this->activeAPIMask = $value;
        return $this;
    }
    /**
     * @param string $databaseName
     *
     * @return UtilRegisterKey Fluent interface.
     */
    public function setDatabaseName(string $databaseName): self
    {
        $this->databaseName = $databaseName;
        return $this;
    }
    /**
     * @param string|int $value
     *
     * @return UtilRegisterKey Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setKeyID($value): self
    {
        if (is_int($value)) {
            $value = (string)$value;
        }
        if (!(is_string($value) && $this->isIntString($value))) {
            $mess = 'KeyID MUST be an integer or integer string but was given (' . gettype($value) . ') ' . $value;
            throw new \InvalidArgumentException($mess);
        }
        $this->keyID = $value;
        return $this;
    }
    /**
     * @param \PDO $value
     *
     * @return UtilRegisterKey Fluent interface.
     */
    public function setPdo(\PDO $value): self
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @param string $tablePrefix
     *
     * @return UtilRegisterKey Fluent interface.
     */
    public function setTablePrefix(string $tablePrefix = ''): self
    {
        $this->tablePrefix = $tablePrefix;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return UtilRegisterKey Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setVCode(string $value): self
    {
        $this->vCode = $value;
        return $this;
    }
    /**
     * @return array
     */
    protected function getColumnNames(): array
    {
        return ['active', 'activeAPIMask', 'keyID', 'vCode'];
    }
    /**
     * @return string
     * @throws \LogicException
     */
    protected function getExistingRegisteredKeyById(): string
    {
        $columns = implode('","', $this->getColumnNames());
        /** @noinspection SqlResolve */
        return sprintf(/** @lang text */
            'SELECT "%4$s" FROM "%1$s"."%2$sutilRegisteredKey" WHERE "keyID"=%3$s',
            $this->databaseName,
            $this->tablePrefix,
            $this->getKeyID(),
            $columns);
    }
    /**
     * @return \PDO
     * @throws \LogicException
     */
    protected function getPdo(): \PDO
    {
        if (null === $this->pdo) {
            $mess = 'Tried to use pdo before it was set';
            throw new \LogicException($mess);
        }
        return $this->pdo;
    }
    /**
     * @return string
     */
    protected function getUpsert(): string
    {
        $columnNames = $this->getColumnNames();
        $columns = implode('","', $columnNames);
        $rowPrototype = '(' . implode(',', array_fill(0, count($columnNames), '?')) . ')';
        $updates = [];
        foreach ($columnNames as $column) {
            $updates[] = '"' . $column . '"=VALUES("' . $column . '")';
        }
        $updates = implode(',', $updates);
        /** @noinspection SqlResolve */
        $sql = sprintf(/** @lang text */
            'INSERT INTO "%1$s"."%2$s%3$s" ("%4$s") VALUES %5$s ON DUPLICATE KEY UPDATE %6$s',
            $this->databaseName,
            $this->tablePrefix,
            'utilRegisteredKey',
            $columns,
            $rowPrototype,
            $updates);
        return $sql;
    }
    /**
     * @return UtilRegisterKey Fluent interface.
     * @throws \LogicException
     */
    protected function initPdo(): self
    {
        $pdo = $this->getPdo();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET SESSION SQL_MODE=\'ANSI,TRADITIONAL\'');
        $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        $pdo->exec('SET SESSION TIME_ZONE=\'+00:00\'');
        $pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_520_ci');
        $pdo->exec('SET COLLATION_CONNECTION=utf8mb4_unicode_520_ci');
        return $this;
    }
    /**
     * @param string $value
     *
     * @return bool
     */
    protected function isIntString(string $value): bool
    {
        $result = str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], '', $value);
        return ('' === $result);
    }
    /**
     * @var bool $active
     */
    private $active;
    /**
     * @var string $activeAPIMask
     */
    private $activeAPIMask;
    /**
     * @var string $databaseName
     */
    private $databaseName;
    /**
     * @var string $keyID
     */
    private $keyID;
    /**
     * @var \PDO $pdo
     */
    private $pdo;
    /**
     * @var string $tablePrefix
     */
    private $tablePrefix;
    /**
     * @var string $vCode
     */
    private $vCode;
}
