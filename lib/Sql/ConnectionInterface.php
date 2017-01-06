<?php
declare(strict_types = 1);
/**
 * Contains interface PDOInterface.
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
namespace Yapeal\Sql;

/**
 * Interface PDOInterface.
 *
 * Classes using this interface should ensure that the instance of PDO connection used is initialized to a mode closest
 * to SQL-92 as possible.
 *
 * __NOTE:__
 *     Only include the methods from \PDO that are used by Yapeal-ng in the interface and did so mostly so IDE had
 *     cleaner examples for type hinting etc as the existing stubs are not as clear as could be plus PHP itself is
 *     unclear in some cases whither or not some methods take parameters or not and how many. I've tried to clean that
 *     up with this interface but it should be considered a WIP.
 */
interface ConnectionInterface
{
    /**
     * Initiates a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function beginTransaction(): bool;
    /**
     * Commits a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function commit(): bool;
    /**
     * Execute an SQL statement and return the number of affected rows.
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
    public function exec(string $statement);
    /**
     * Checks if inside a transaction.
     *
     * @return bool <b>true</b> if a transaction is currently active, and <b>false</b> if not.
     */
    public function inTransaction(): bool;
    /**
     * Flag to indicate if non-wrapper methods of underlying \PDO connection can be used.
     *
     * @return bool
     */
    public function isExposingPdo(): bool;
    /**
     * Flag to mark if the under-laying PDO driver has already been set to it's most SQL-92 compatible mode or not.
     *
     * @return bool
     */
    public function isSql92Mode(): bool;
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
    public function prepare(string $statement, array $driver_options = []);
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * __NOTE:__
     *     According to PHP this method doesn't accept any parameters including $statement which would make it kind of
     *     pointless. After take a look at the code for PDO I believe it has to do with a bug in how they try forcing an
     *     error for the no parameters case but it's some what unclear what's going on really. I'm going to give it
     *     something cleaner here and deal with it in Connection class with wrapper.
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
    public function query(string $statement, int $mode = \PDO::ATTR_DEFAULT_FETCH_MODE, ...$arguments);
    /**
     * Rolls back a transaction.
     *
     * @return bool <b>true</b> on success or may return <b>false</b> or emits <b>\PDOException</b> (depending on error
     * handling).
     * @throws \PDOException
     */
    public function rollBack(): bool;
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
    public function setAttribute(int $attribute, $value): bool;
}
