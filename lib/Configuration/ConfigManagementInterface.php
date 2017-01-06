<?php
declare(strict_types = 1);
/**
 * Contains interface ConfigManagementInterface.
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
 * Interface ConfigManagementInterface.
 *
 * The interface for a CRUD style of managing the config files used in composing the settings.
 */
interface ConfigManagementInterface
{
    /**
     * Add a new config file candidate to be used during the composing of settings.
     *
     * This method is expected to be used with the update() method to change the config files use during the composing
     * of settings.
     *
     * Though Yapeal-ng considers and treats all configuration files as optional the individual settings themselves are
     * not and many of them if missing can cause it to not start, to fail, or possible cause other undefined behavior
     * to happen instead.
     *
     *
     *
     * @param string $pathName Configuration file name with absolute path.
     * @param int    $priority An integer in the range 0 - PHP_INT_MAX with large number being a higher priority.
     *                         The range between 100 and 10000 are reserved for application developer use with
     *                         everything else reserved for internal use only.
     * @param bool   $watched  Flag to tell if file should be monitored for changes and updates or read initially and
     *                         future changes ignored. Note that the $force flag of update() can be used to override
     *                         this parameter.
     *
     * @throws \InvalidArgumentException Throws this exception if you try adding the same $pathFile again. Use
     *                                   hasConfigFile() to see if entry already exists.
     */
    public function addConfigFile(string $pathName, int $priority = 1000, bool $watched = true);
    /**
     * The Create part of the CRUD interface.
     *
     * Creates a new Yapeal-ng config composed from the settings found in the given current config file(s). This would
     * be the closest to the original mode of Yapeal-ng where all the config files are processed once and then use for
     * the rest of the time. Both in the classic cron/scheduled task and when using 'yc Y:A' (Yapeal:AutoMagic) command
     * this is the closest match to how they worked. All existing settings from the current known config files will be
     * forgotten and the $configFiles list will be used to compose the new collection of settings.
     *
     * If you just need to update the processed config files look at using update() combined with addConfigFiles() and
     * removeConfigFile().
     *
     * One or more config file(s) must have been given and there must be some actual settings found after they have
     * been processed or an exception will be thrown.
     *
     * The $configFiles parameter can be just a plain list (array) of config file names with directory paths. If given
     * a plain list like this Yapeal-ng will use the default priority and watched modes as seen in the addConfigFile()
     * method. An example of this would look something like this:
     *
     * <code>
     * <?php
     * ...
     * // @var ConfigManagementInterface $config
     * $configFiles = [
     *     __DIR__ . '/yapealDefaults.yaml',
     *     dirname(__DIR__, 2) . '/config/yapeal.yaml'
     * ];
     * $config->create($configFiles);
     * ...
     * </code>
     *
     * An example that includes optional priority and watched flags:
     * <code>>
     * <?php
     * ...
     * // @var ConfigManagementInterface $config
     * $configFiles = [
     *     ['pathName' => __DIR__ . '/yapealDefaults.yaml', 'priority' => PHP_INT_MAX, 'watched' => false],
     *     ['pathName' => dirname(__DIR__, 2) . '/config/yapeal.yaml', 'priority' => 10],
     *     ['pathName' => __DIR__ . '/special/run.yaml']
     * ];
     * $config->create($configFiles);
     * ...
     * </code>
     *
     * Including either 'priority' or 'watched' is optional and they will receive the default value from addConfigFile()
     * if not given.
     *
     * @param array $configFiles A list of config file names with optional priority and watched flag. See example for
     *                           how to include them.
     *
     * @return bool
     */
    public function create(array $configFiles): bool;
    /**
     * The Delete part of the CRUD interface.
     *
     * This both removes all the candidate config files and removes all of their settings so the Container retains only
     * those settings it originally had when given. This does _not_ necessarily mean it is fully reset. The reason this
     * can't provide a complete reset is that while the other config files were being used their settings might have
     * been used in any created callable instances or as substitutions in the original. The only way to insure this
     * does not happen would be to not use any substitutions or other settings from outside the original Container
     * ones. This shouldn't be an issue as by default only an empty or nearly empty Container is normal given to the
     * ConfigManager instance. I just wanted to clearly document this effect to remind myself and anyone else to use
     * care when giving a non-empty Container to the ConfigManager instance and the ripple effects they can have and be
     * effected by other things.
     *
     * @return bool
     */
    public function delete(): bool;
    /**
     * Allows checking if a config file candidate has already been added.
     *
     * @param string $pathName
     *
     * @return bool Returns true if candidate entry exist, false if unknown.
     */
    public function hasConfigFile(string $pathName): bool;
    /**
     * The Read part of the CRUD interface.
     *
     * Since the Container where the settings are kept is one of the main shared objects inside Yapeal-ng this is mostly
     * redundant but used this method as a way to return only stuff added by the config files.
     *
     * @return array
     */
    public function read(): array;
    /**
     * Remove an existing config file candidate entry.
     *
     * @param string $pathName
     *
     * @return array Return the removed config file candidate entry with 'priority' and 'watch'.
     * @throws \InvalidArgumentException Throw this exception if there is no matching entry found. Use hasConfigFile()
     *                                   to check if the candidate config file entry exists.
     */
    public function removeConfigFile(string $pathName): array;
    /**
     * @param array $value
     *
     * @return self Fluent interface
     */
    public function setSettings(array $value = []);
    /**
     * The Update part of the CRUD interface.
     *
     * It is expected that this will see little or no use if Yapeal-ng is being used in the typical/original mode via
     * direct calls to the Yapeal::autoMagic() method or manually running 'yc Y:A' from the command line but this
     * method is expected to be used in a planned future Yapeal-ng daemon. This planned new daemon is one of the main
     * reasons this interface and the implementing class are being created so it can be signaled to re-read it's
     * configuration or even watch and auto-update it's configuration when it notices changes to any of the given
     * config files.
     *
     * Note that it expected that the addConfigFile() and removeConfigFile() methods have been called already to change
     * which config files will be used to compose the new settings.
     *
     * @param bool $force        Used to force re-reading of the known config file(s) including the unwatched ones.
     *                           This can be thought of as running create() but without having to give a complete list
     *                           of config files again.
     *
     * @return bool
     */
    public function update(bool $force = false): bool;
}
