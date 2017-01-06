<?php
declare(strict_types = 1);
/**
 * Contains class ManageRegisteredKey.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016-2017 Michael Cummings
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\AdminTools;

use Yapeal\Event\MediatorInterface;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Sql\ConnectionInterface;

/**
 * Class ManageRegisteredKey provides CRUD access to the RegisteredKey table.
 */
class ManageRegisteredKey
{
    /**
     * ManageRegisteredKey constructor.
     *
     * @param CommonSqlQueries    $csq
     * @param ConnectionInterface $pdo
     * @param MediatorInterface   $yem
     */
    public function __construct(CommonSqlQueries $csq, ConnectionInterface $pdo, MediatorInterface $yem)
    {
        $this->csq = $csq;
        $this->pdo = $pdo;
        $this->yem = $yem;
        $this->columnNames = ['active', 'activeAPIMask', 'keyID', 'vCode'];
    }
    /**
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     */
    public function commit(): bool
    {
        $columnNames = ['active', 'activeAPIMask', 'keyID', 'vCode'];
        try {
            switch ($this->command) {
                case 'create':
                    $sql = $this->csq->getInsert('yapealRegisteredKey', $columnNames, 1);
                    break;
                case 'read':
                    $sql = $this->csq->getSelect('yapealRegisteredKey', $columnNames, ['keyID' => $this->keyID]);
                    return $this->readFromTable($sql);
                case 'update':
                    $sql = $this->csq->getUpsert('yapealRegisteredKey', $columnNames, 1);
                    break;
                case 'delete':
                    $sql = $this->csq->getDeleteFromTableWithKeyID('yapealRegisteredKey', $this->keyID);
                    break;
                default:
                    $this->lastErrorString = 'Unknown command';
                    return false;
                    break;
            }
        } catch (\BadMethodCallException $exc) {
            $this->lastErrorString = 'Failed to get SQL for ' . $this->command;
            return false;
        }
        if (!$this->executeCommandSql($sql)) {
            return false;
        }
        $this->command = '';
        $this->lastErrorString = '';
        $this->setDirty(false);
        return true;
    }
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * @param int    $keyID
     * @param bool   $active
     * @param int    $activeAPIMask
     * @param string $vCode
     *
     * @return self Fluent interface.
     */
    public function create(int $keyID, bool $active, int $activeAPIMask, string $vCode): self
    {
        $this->active = $active;
        $this->activeAPIMask = $activeAPIMask;
        $this->keyID = $keyID;
        $this->vCode = $vCode;
        $this->command = 'create';
        $this->setDirty();
        return $this;
    }
    /**
     * @param int $keyID
     *
     * @return self Fluent interface.
     */
    public function delete(int $keyID): self
    {
        $this->keyID = $keyID;
        $this->command = 'delete';
        $this->setDirty();
        return $this;
    }
    /**
     * @return string
     */
    public function getLastErrorString(): string
    {
        return $this->lastErrorString;
    }
    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return '' === $this->lastErrorString;
    }
    /**
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->dirty;
    }
    /**
     * @param int  $keyID
     *
     * @param bool $refresh
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     */
    public function read(int $keyID, bool $refresh = false): array
    {
        if ($keyID !== $this->keyID) {
            $this->keyID = $keyID;
            $refresh = true;
        }
        if ($refresh) {
            $this->command = 'read';
            $this->setDirty(true);
            $this->commit();
        }
        return [
            'active' => $this->active,
            'activeAPIMask' => $this->activeAPIMask,
            'keyID' => $keyID,
            'vCode' => $this->vCode
        ];
    }
    /**
     * @param bool|null   $active
     * @param int|null    $activeAPIMask
     * @param string|null $vCode
     *
     * @return self Fluent interface.
     */
    public function update(bool $active = null, int $activeAPIMask = null, string $vCode = null): self
    {
        $this->active = $active;
        $this->activeAPIMask = $activeAPIMask;
        $this->vCode = $vCode;
        $this->command = 'update';
        $this->setDirty();
        return $this;
    }
    /**
     * @param array $columns
     *
     * @return array
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    private function enforceColumnTypesAndStructure(array $columns): array
    {
        array_walk($columns,
            function (&$value, $key) {
                switch ($key) {
                    case 'active':
                        $value = (bool)$value;
                        break;
                    case 'activeAPIMask':
                    case 'keyID':
                        $value = (int)$value;
                        break;
                    case 'vCode':
                        $value = (string)$value;
                        break;
                    default:
                        $mess = 'Given unknown value ' . $key;
                        throw new \UnexpectedValueException($mess);
                }
            });
        if (count($this->columnNames) > count($columns)) {
            $mess = 'Missing one or more of the required values: "' . implode('","', $this->columnNames) . '"';
            $mess .= ' Was given "' . implode('","', array_keys($columns)) . '"';
            throw new \InvalidArgumentException($mess);
        }
        return $columns;
    }
    /**
     * @param string $sql
     *
     * @return bool
     * @throws \PDOException
     */
    private function executeCommandSql(string $sql)
    {
        try {
            if (!$this->pdo->beginTransaction()) {
                $this->lastErrorString = 'Failed to start transaction for ' . $this->command;
                return false;
            }
            $stmt = $this->pdo->prepare($sql);
            if (!$stmt->execute([$this->active ?: 0, $this->activeAPIMask, $this->keyID, $this->vCode])) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                $this->lastErrorString = 'Failed to execute prepared query for ' . $this->command;
                return false;
            }
            if (!$this->pdo->commit()) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                $this->lastErrorString = 'Failed to commit the transaction for ' . $this->command;
                return false;
            }
        } catch (\PDOException $exc) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->lastErrorString = $exc->getMessage();
            return false;
        }
        return true;
    }
    /**
     * @param string $sql
     *
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \UnexpectedValueException
     */
    private function readFromTable(string $sql): bool
    {
        $stmt = $this->pdo->query($sql);
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (1 !== count($columns)) {
            $this->lastErrorString = 'Expected to fetch a single row for ' . $this->command;
            return false;
        }
        $columns = $this->enforceColumnTypesAndStructure($columns[0]);
        foreach ($columns as $index => $column) {
            /** @noinspection PhpVariableVariableInspection */
            $this->$index = $column;
        }
        $this->command = '';
        $this->setDirty(false);
        return true;
    }
    /**
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    private function setDirty(bool $value = true): self
    {
        $this->dirty = $value;
        return $this;
    }
    /**
     * @var bool $active
     */
    private $active = false;
    /**
     * @var int $activeAPIMask
     */
    private $activeAPIMask = 0;
    /**
     * @var array $columnNames
     */
    private $columnNames;
    /**
     * @var string $command
     */
    private $command = '';
    /**
     * @var CommonSqlQueries $csq
     */
    private $csq;
    /**
     * Used to track if the current class data is not synced with the table yet.
     *
     * @var bool $dirty
     */
    private $dirty = true;
    /**
     * @var int $keyID
     */
    private $keyID;
    /**
     * @var string $lastErrorString
     */
    private $lastErrorString = '';
    /**
     * @var ConnectionInterface $pdo
     */
    private $pdo;
    /**
     * @var string $vCode
     */
    private $vCode = '';
    /**
     * @var MediatorInterface $yem
     */
    private $yem;
}
