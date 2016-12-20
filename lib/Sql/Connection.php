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
 * Class Connection.
 *
 * This is a transparent wrapper around \PDO. Used to make testing easier and have place to implement marker interface.
 */
class Connection extends \PDO implements PDOInterface
{
    /**
     * Connection constructor.
     *
     * @param string      $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array|null  $options
     */
    public function __construct(string $dsn, string $username = null, string $password = null, array $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->sql92Mode = false;
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
     * @var bool $sql92Mode
     */
    private $sql92Mode;
}
