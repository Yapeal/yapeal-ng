<?php
declare(strict_types = 1);
/**
 * Contains class MockSqlCleanup.
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
namespace Spec\Yapeal\Sql;

use Yapeal\Sql\SqlCleanupTrait;

/**
 * Class MockSqlCleanup.
 */
class MockSqlCleanup
{
    use SqlCleanupTrait;
    /**
     * MockSqlSubs constructor.
     */
    public function __construct()
    {
        // Dummy
    }
    /**
     * Proxy to allowed access to protected trait method for testing.
     *
     * @param string $sql
     * @param array  $replacements
     *
     * @return string
     */
    public function proxyGetCleanedUpSql(string $sql, array $replacements): string
    {
        return $this->getCleanedUpSql($sql, $replacements);
    }
}
