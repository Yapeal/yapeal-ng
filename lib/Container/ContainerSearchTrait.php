<?php
declare(strict_types = 1);
/**
 * Contains trait ContainerSearchTrait.
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
namespace Yapeal\Container;

/**
 * Trait ContainerSearchTrait.
 */
trait ContainerSearchTrait
{
    /**
     * @return ContainerInterface
     */
    abstract public function getDic(): ContainerInterface;
    /**
     * Find a list of keys having the given prefix.
     *
     * @param string $prefix
     *
     * @return array
     */
    public function findKeysByPrefix(string $prefix): array
    {
        $callback = function (string $key) use ($prefix) {
            return 0 === strpos($key, $prefix);
        };
        return array_filter($this->getDic()->keys(), $callback);
    }
    /**
     * Get an assoc array filtered by key prefix.
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getArrayByKeyPrefix(string $prefix): array
    {
        $keys = $this->findKeysByPrefix($prefix);
        if (0 === count($keys)) {
            return [];
        }
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->getDic()[$key];
        }
        return $result;
    }
}
