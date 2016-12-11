<?php
declare(strict_types = 1);
/**
 * Contains trait SqlSubsTrait.
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

use Yapeal\Container\ContainerInterface;

/**
 * Trait SqlSubsTrait.
 */
trait SqlSubsTrait
{
    /**
     * Takes one or more SQL statements with comments and normalizes EOLs and also removes most comments.
     *
     * NOTE: Any comment 'code' like used by MySQL are also removed.
     *
     * @param string $sql
     * @param array  $replacements Expects associative array like from getSqlSubs().
     *
     * @return string
     */
    protected function getCleanedUpSql(string $sql, array $replacements): string
    {
        // Remove multi-space indents.
        do {
            $sql = str_replace("\n  ", "\n ", $sql, $count);
        } while (0 < $count);
        $replacements = array_reverse($replacements);
        $replacements["\n)"] = ')';
        $replacements["\n "] = ' ';
        $replacements["\r\n"] = "\n";
        $replacements = array_reverse($replacements);
        // Normalize line ends and change pretty multiple lines sql and comments into single line ones
        // and do replacements.
        $sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
        /**
         * @var string[] $lines
         */
        $lines = explode("\n", $sql);
        // Filter out non-sql lines like comments and blank lines.
        $lines = array_filter($lines,
            function ($line) {
                $line = trim($line);
                $nonSql = '' === $line
                    || 0 === strpos($line, '--')
                    || (0 === strpos($line, '/* ') && 0 === strpos(strrev($line), '/*'))
                    || (0 === strpos($line, '/** ') && 0 === strpos(strrev($line), '/*'));
                return !$nonSql;
            });
        return implode("\n", $lines);
    }
    /**
     * Uses Sql section settings to make a filtered list of replacement pairs for SQL statements.
     *
     * @param ContainerInterface $dic
     *
     * @return array
     */
    protected function getSqlSubs(ContainerInterface $dic)
    {
        $keys = $dic->keys();
        $platform = '.' . $dic['Yapeal.Sql.platform'];
        /**
         * @var array $filteredKeys
         */
        $filteredKeys = array_filter($keys,
            function ($key) use ($platform) {
                if (0 !== strpos($key, 'Yapeal.Sql.')) {
                    return false;
                }
                $filtered = in_array($key, ['Yapeal.Sql.Callable.CommonQueries', 'Yapeal.Sql.Callable.Connection', 'Yapeal.Sql.Callable.Creator'], true)
                    || false !== strpos($key, 'Classes.')
                    || (false !== strpos($key, 'Platforms.') && false === strpos($key, $platform));
                return !$filtered;
            });
        $replacements = [];
        foreach ($filteredKeys as $key) {
            $subName = '{' . substr($key, strrpos($key, '.') + 1) . '}';
            $replacements[$subName] = $dic[$key];
        }
        return $replacements;
    }
}
