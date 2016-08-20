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
        while (false !== strpos($sql, "\n  ")) {
            $sql = str_replace("\n  ", "\n ", $sql);
        }
        $replacements = array_reverse($replacements);
        $replacements["\n)"] = ')';
        $replacements["\n "] = ' ';
        $replacements["\n    "] = ' ';
        $replacements["\r\n"] = "\n";
        $replacements = array_reverse($replacements);
        // Normalize line ends and change pretty multiple lines sql and comments into single line ones
        // and do replacements.
        $sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
        /**
         * @var string[] $statements
         */
        $statements = explode("\n", $sql);
        // Filter out non-sql lines like comments and blank lines.
        $statements = array_filter($statements,
            function ($statement) {
                /** @noinspection IfReturnReturnSimplificationInspection */
                if (0 === strpos($statement, '-- ')
                    || '' === trim($statement)
                    || (0 === strpos($statement, '/* ') && 0 === strpos(strrev($statement), '/*'))
                ) {
                    return false;
                }
                return true;
            });
        return implode("\n", $statements);
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
        $filteredKeys = array_filter($keys,
            function ($key) use ($platform) {
                $classes = ['Yapeal.Sql.CommonQueries', 'Yapeal.Sql.Connection', 'Yapeal.Sql.Creator'];
                $isPlatform = false !== strpos($key, $platform);
                $hasPlatforms = false !== strpos($key, 'Platforms.');
                $isHandlers = false !== strpos($key, 'Handlers.');
                if ($isHandlers || ($hasPlatforms && !$isPlatform)) {
                    return false;
                }
                return !(false === strpos($key, 'Yapeal.Sql.') || in_array($key, $classes, true));
            });
        $replacements = [];
        foreach ($filteredKeys as $key) {
            $subName = '{' . substr($key, strrpos($key, '.') + 1) . '}';
            $replacements[$subName] = $dic[$key];
        }
        return $replacements;
    }
}
