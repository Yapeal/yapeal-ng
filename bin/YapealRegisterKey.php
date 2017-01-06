<?php
declare(strict_types = 1);
/**
 * Contains YapealRegisterKey class.
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
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal;

use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Sql\ConnectionInterface;

/**
 * Class YapealRegisterKey
 *
 * WARNING: This class changes the PDO connection into MySQL's ANSI,TRADITIONAL
 * mode and makes other changes that may cause other queries in any of your
 * own application code that tries using the connection after the changes to
 * fail.
 *
 * For example if you use things like back-tick quotes in queries they may
 * cause the query to fail or issue warnings. You can find out more about MySQL
 * modes at
 * {@link http://dev.mysql.com/doc/refman/5.5/en/sql-mode.html}
 */
class YapealRegisterKey
{
    /**
     * @param ConnectionInterface $pdo
     * @param string              $databaseName
     * @param string              $tablePrefix
     */
    public function __construct(ConnectionInterface $pdo, string $databaseName = 'yapeal-ng', string $tablePrefix = '')
    {
        $this->setPdo($pdo)
            ->setDatabaseName($databaseName)
            ->setTablePrefix($tablePrefix);
    }
    /**
     * Return string that can be use for GET or POST query or for not so nice user display.
     *
     * @return string
     * @throws \LogicException
     */
    public function __toString(): string
    {
        return http_build_query($this->getAsArray());
    }
    /**
     * @return bool
     * @throws \PDOException
     */
    public function delete(): bool
    {
        try {
            $sql = sprintf(/** @lang text */
                'DELETE FROM "%s"."%s%s" WHERE "keyID" = %s',
                $this->databaseName,
                $this->tablePrefix,
                'yapealRegisteredKey',
                $this->getKeyID());
            if (1 !== $this->getPdo()
                    ->exec($sql)
            ) {
                return false;
            }
        } catch (\LogicException $exc) {
            // KeyID or PDO not being set returns false.
            return false;
        }
        return true;
    }
    /**
     * @return int
     * @throws \LogicException
     */
    public function getActive(): int
    {
        return (int)$this->isActive();
    }
    /**
     * @return int
     * @throws \LogicException
     */
    public function getActiveAPIMask(): int
    {
        if (null === $this->activeAPIMask) {
            $mess = 'Tried to access "activeAPIMask" before it was set';
            throw new \LogicException($mess);
        }
        return $this->activeAPIMask;
    }
    /**
     * @param bool $activeIsBool
     *
     * @return array
     * @throws \LogicException
     */
    public function getAsArray($activeIsBool = false): array
    {
        return [
            'active' => $activeIsBool ? $this->isActive() : $this->getActive(),
            'activeAPIMask' => $this->getActiveAPIMask(),
            'keyID' => $this->getKeyID(),
            'vCode' => $this->getVCode()
        ];
    }
    /**
     * @param bool $activeIsBool
     *
     * @return string
     * @throws \LogicException
     */
    public function getAsJson($activeIsBool = false): string
    {
        return json_encode($this->getAsArray($activeIsBool));
    }
    /**
     * @return int
     * @throws \LogicException
     */
    public function getKeyID(): int
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
     * @return self Fluent interface.
     * @throws YapealDatabaseException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \PDOException
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
        return $this->setFromArray($result[0]);
    }
    /**
     * Method used to persist changes to the database.
     *
     * NOTE: After calling this method the MySQL PDO connection will be
     * switched to ANSI mode and use UTF-8.
     *
     * @see UtilRegisteredKey
     * @return self Fluent interface.
     * @throws \LogicException
     * @throws \PDOException
     */
    public function save(): self
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
     * @return self Fluent interface.
     */
    public function setActive(bool $value = true): self
    {
        $this->active = $value;
        return $this;
    }
    /**
     * @param int $value
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setActiveAPIMask(int $value): self
    {
        $this->activeAPIMask = $value;
        return $this;
    }
    /**
     * @param string $databaseName
     *
     * @return self Fluent interface.
     */
    public function setDatabaseName(string $databaseName): self
    {
        $this->databaseName = $databaseName;
        return $this;
    }
    /**
     * @param array $key
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setFromArray(array $key): self
    {
        $diff = array_diff($this->getColumnNames(), array_keys($key));
        if (0 !== count($diff)) {
            $mess = sprintf('The given array is missing ', implode(', ', $diff));
            throw new \InvalidArgumentException($mess);
        }
        $this->setActive((bool)$key['active'])
            ->setActiveAPIMask((int)$key['activeAPIMask'])
            ->setKeyID((int)$key['keyID'])
            ->setVCode((string)$key['vCode']);
        return $this;
    }
    /**
     * @param string $json
     *
     * @throws \InvalidArgumentException
     */
    public function setFromJson(string $json)
    {
        $this->setFromArray(json_decode($json));
    }
    /**
     * @param int $value
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setKeyID(int $value): self
    {
        $this->keyID = $value;
        return $this;
    }
    /**
     * @param ConnectionInterface $value
     *
     * @return self Fluent interface.
     */
    public function setPdo(ConnectionInterface $value): self
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @param string $tablePrefix
     *
     * @return self Fluent interface.
     */
    public function setTablePrefix(string $tablePrefix = ''): self
    {
        $this->tablePrefix = $tablePrefix;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self Fluent interface.
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
    private function getColumnNames(): array
    {
        return ['active', 'activeAPIMask', 'keyID', 'vCode'];
    }
    /**
     * @return string
     * @throws \LogicException
     */
    private function getExistingRegisteredKeyById(): string
    {
        $columns = implode('","', $this->getColumnNames());
        /** @noinspection SqlResolve */
        return sprintf(/** @lang text */
            'SELECT "%s" FROM "%s"."%s%s" WHERE "keyID"=%s',
            $columns,
            $this->databaseName,
            $this->tablePrefix,
            'yapealRegisteredKey',
            $this->getKeyID());
    }
    /**
     * @return ConnectionInterface
     * @throws \LogicException
     */
    private function getPdo()
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
    private function getUpsert(): string
    {
        $columnNames = $this->getColumnNames();
        $columns = implode('","', $columnNames);
        $rowPrototype = '(' . implode(',', array_fill(0, count($columnNames), '?')) . ')';
        $updates = [];
        foreach ($columnNames as $column) {
            $updates[] = '"' . $column . '"=VALUES("' . $column . '")';
        }
        $updates = implode(',', $updates);
        $sql = sprintf(/** @lang text */
            'INSERT INTO "%s"."%s%s" ("%s") VALUES %s ON DUPLICATE KEY UPDATE %s',
            $this->databaseName,
            $this->tablePrefix,
            'yapealRegisteredKey',
            $columns,
            $rowPrototype,
            $updates);
        return $sql;
    }
    /**
     * @return self Fluent interface.
     * @throws \LogicException
     * @throws \PDOException
     */
    private function initPdo(): self
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
     * @var bool $active
     */
    private $active;
    /**
     * @var int $activeAPIMask
     */
    private $activeAPIMask;
    /**
     * @var string $databaseName
     */
    private $databaseName;
    /**
     * @var int $keyID
     */
    private $keyID;
    /**
     * @var ConnectionInterface $pdo
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
