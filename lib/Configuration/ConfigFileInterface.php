<?php
declare(strict_types = 1);
/**
 * Contains interface ConfigFileInterface.
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
namespace Yapeal\Configuration;

/**
 * Class YamlConfigFile.
 */
interface ConfigFileInterface
{
    /**
     * Flatten array to a single dimension where the new key contains the original keys joined together by a '.'.
     *
     * @param array|null $yaml The array to be flattened. If null assumes $settings.
     *
     * @return array
     */
    public function flattenYaml(array $yaml = null): array;
    /**
     * Getter for path file.
     *
     * @return string File name with absolute path.
     */
    public function getPathFile(): string;
    /**
     * Getter for complete list of settings.
     *
     * @return array
     */
    public function getSettings(): array;
    /**
     * Used to read data from the config file.
     *
     * @return self Fluent interface.
     * @throws \BadMethodCallException Throws exception if path file isn't set.
     */
    public function read();
    /**
     * Used to save data to config file.
     *
     * @throws \BadMethodCallException Throws exception if path file isn't set.
     */
    public function save();
    /**
     * Used to set or reset the config file path name.
     *
     * @param string|null $value File name with absolute path.
     */
    public function setPathFile(string $value = null);
    /**
     * Used to give settings in mass.
     *
     * @param array $value A multi-dimensional assoc array.
     */
    public function setSettings(array $value = []);
    /**
     * Expands any keys containing '.' into a multi-dimensional assoc array and their values.
     *
     * @param array|null $yaml The array to be unflattened. If null assumes $settings.
     *
     * @return array
     */
    public function unflattenYaml(array $yaml = null): array;
}
