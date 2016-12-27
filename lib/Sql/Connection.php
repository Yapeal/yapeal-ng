<?php
declare(strict_types = 1);
/**
 * Contains class Connection.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016 Michael Cummings
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\Sql;

/**
 * Class Connection is a wrapper for an underlying \PDO instance.
 *
 * This was suppose to just be a transparent wrapper around \PDO with extends. Plan was to have something to make
 * testing easier and have place to implement an interface but instead I've had to go beyond that to make it work like I
 * wanted it to.
 */
class Connection implements ConnectionInterface
{
    /**
     * Connection constructor.
     *
     * @param string      $dsn      The Data Source Name, or DSN, contains the information required to connect to the
     *                              database. In general, a DSN consists of the PDO driver name, followed by a colon,
     *                              followed by the PDO driver-specific connection syntax. See the docs
     *                              for<b>\PDO::__construct()</b> for full details.
     * @param string|null $username The user name for the DSN string. This parameter is optional for some PDO drivers.
     *                              Example: SqlLite since it just a file in a directory somewhere it doesn't need
     *                              this.
     * @param string|null $password The password for the DSN string. This parameter is optional for some PDO drivers.
     *                              Example: SqlLite since it just a file in a directory somewhere it doesn't need
     *                              this.
     * @param array|null  $options  A key=>value array of driver-specific connection options.
     *
     * @throws \PDOException The \PDO constructor always throws an exception even if you are setting it to use errors
     *                       instead. That setting will only effect any of the follow interacts with \PDO.
     * @see \PDO
     */
    public function __construct(string $dsn, string $username = null, string $password = null, array $options = null)
    {
        $this->pdo = new \PDO($dsn, $username, $password, $options);
        $this->exposingPdo = false;
        $this->sql92Mode = false;
    }
    /**
     * Allow any other underlying \PDO methods be called without having to wrap them all.
     *
     * The wrapper methods of this class are expected to cover what Yapeal-ng itself uses from \PDO but allows the rest
     * as well through this method.
     *
     * @param string $name      Name of any \PDO methods not defined above.
     * @param array  $arguments Method arguments as a name=>value array.
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->pdo, $name)) {
            throw new \BadMethodCallException('Unknown method ' . $name);
        }
        if (!$this->isExposingPdo()) {
            $mess = 'Call to an unexposed but valid \PDO method if you need to use it insure exposingPdo is true';
            throw new \BadMethodCallException($mess);
        }
        return call_user_func_array([$this->pdo, $name], $arguments);
    }
    /**
     * Initiates a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function beginTransaction(): bool
    {
        return (bool)$this->pdo->beginTransaction();
    }
    /**
     * Commits a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function commit(): bool
    {
        return (bool)$this->pdo->commit();
    }
    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * Wrapper to allow proper type hinting.
     *
     * @param string $statement This must be a valid SQL statement for the target database server. This can _not_ be
     *                          used with anything that returns a result set like SELECT etc. If you need that use
     *                          <b>\PDO::query</b> or preferably <b>\PDO::prepare</b> and <b>\PDOStatement:execute</b>
     *                          for better safety and improved repeated query handling.
     *
     * @return int|false <b>\PDO::exec</b> returns the number of rows that were modified or deleted by the SQL statement
     * you issued. If no rows were affected, <b>\PDO::exec</b> returns 0. <b>\PDO::exec<b> may return <b>false<b> or
     * emits <b>\PDOException</b> (depending on error handling).
     * @throws \PDOException
     */
    public function exec(string $statement)
    {
        return $this->pdo->exec($statement);
    }
    /**
     * Checks if inside a transaction.
     *
     * @return bool <b>true</b> if a transaction is currently active, and <b>false</b> if not.
     */
    public function inTransaction(): bool
    {
        return (bool)$this->pdo->inTransaction();
    }
    /**
     * Flag to indicate if non-wrapper methods of underlying \PDO connection can be used.
     *
     * @return bool
     */
    public function isExposingPdo(): bool
    {
        return $this->exposingPdo;
    }
    /**
     * Flag to mark if the under-laying PDO driver has already been set to it's most SQL-92 compatible mode or not.
     *
     * @return bool
     */
    public function isSql92Mode(): bool
    {
        return $this->sql92Mode;
    }
    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $statement      This must be a valid SQL statement for the target database server.
     * @param array  $driver_options This array holds one or more key=>value pairs to set attribute values for the
     *                               <b>\PDOStatement</b> object that this method returns. You would most commonly use
     *                               this to set the <b>\PDO::ATTR_CURSOR</b> value to <b>\PDO::CURSOR_SCROLL</b> to
     *                               request a scrollable cursor. Some drivers have driver specific options that may be
     *                               set at prepare-time.
     *
     * @return \PDOStatement|false If the database server successfully prepares the statement, <b>\PDO::prepare</b>
     * returns a <b>\PDOStatement</b> object. If the database server cannot successfully prepare the statement,
     * <b>\PDO::prepare</b> returns <b>false</b> or emits <b>\PDOException</b> (depending on error handling).
     * @throws \PDOException
     */
    public function prepare(string $statement, $driver_options = null)
    {
        return $this->pdo->prepare($statement, $driver_options);
    }
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * Wrapper do to the reasons given in NOTE.
     *
     * __NOTE:__
     *     According to PHP this method doesn't accept any parameters including $statement which would make it kind of
     *     pointless. After take a look at the code for PDO I believe it has to do with a bug in how they try forcing an
     *     error for the no parameters case but it's some what unclear what's going on really. I'm going to give it
     *     something cleaner here and deal with it in Connection class wrapper.
     *
     * @param string $statement       The SQL statement to prepare and execute.
     * @param int    $mode            The fetch mode must be one of the \PDO::FETCH_* constants.
     * @param mixed  $arguments       The second and following parameters are the same as the parameters for
     *                                \PDOStatement::setFetchMode.
     *
     * @return false|\PDOStatement <b>\PDO::query</b> returns a PDOStatement object, or <b>\PDO::query</b> may return
     * <b>false</b> or emits <b>\PDOException</b> (depending on error handling).
     * @throws \PDOException
     * @see \PDOStatement::setFetchMode For a full description of the second and following parameters.
     */
    public function query(string $statement, int $mode = \PDO::FETCH_BOTH, ...$arguments)
    {
        if (0 === count($arguments)) {
            return $this->pdo->query($statement, $mode);
        }
        return $this->pdo->query($statement, $mode, ...$arguments);
    }
    /**
     * Rolls back a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function rollBack(): bool
    {
        return (bool)$this->pdo->rollBack();
    }
    /**
     * Set an attribute.
     *
     * @param int   $attribute
     * @param mixed $value
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function setAttribute(int $attribute, $value): bool
    {
        return (bool)$this->pdo->setAttribute($attribute, $value);
    }
    /**
     * Used to decide if method pass through to the underlying \PDO connect are allowed for non-wrapper methods.
     *
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    public function setExposingPdo(bool $value = false): self
    {
        $this->exposingPdo = $value;
        return $this;
    }
    /**
     * @param bool $value
     *
     * @return self Fluent interface
     */
    public function setSql92Mode(bool $value = true): self
    {
        $this->sql92Mode = $value;
        return $this;
    }
    /**
     * @var bool $exposingPdo
     */
    private $exposingPdo;
    /**
     * @var \PDO $pdo
     */
    private $pdo;
    /**
     * @var bool $sql92Mode
     */
    private $sql92Mode;
}

