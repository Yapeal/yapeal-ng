<?php
declare(strict_types = 1);
/**
 * Contains class ConfigManager.
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

use Yapeal\Container\ContainerInterface;

/**
 * Class ConfigManager.
 */
class ConfigManager implements ConfigManagementInterface
{
    /**
     * Default priority used with addConfigFile method.
     */
    const PRIORITY_DEFAULT = 1000;
    /**
     * ConfigManager constructor.
     *
     * @param ContainerInterface $dic Container instance.
     * @param array              $settings Additional parameters or objects from non-config file source. Normally only
     *                                     used internal by Yapeal-ng for things that need to be added but it should
     *                                     still be possible for an application developer to override them so they can
     *                                     not be added to the Container directly and end up being protected. Mostly
     *                                     things that need to be done at run time like cache/ and log/ directory paths.
     */
    public function __construct(ContainerInterface $dic, array $settings = [])
    {
        $this->addedSettings = [];
        $this->configFiles = [];
        $this->matchYapealOnly = false;
        $this->setDic($dic);
        $this->settings = $settings;
    }
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * Add a new config file candidate to be used during the composing of settings.
     *
     * This method is expected to be used with the update() method to change the config files used during the composing
     * of settings.
     *
     * Though Yapeal-ng considers and treats all configuration files as optional the individual settings themselves are
     * not and many of them if missing can cause it to not start, to fail, or possible cause other undefined behavior
     * to happen instead.
     *
     * The behavior when adding the same config file with an absolute and relative path or more than one relative path
     * is undefined and may change and is considered unsupported. It makes little sense to do so anyway but mentioned
     * here so developers known to watch for this edge case.
     *
     * @param string $pathFile Configuration file name with absolute path.
     * @param int    $priority An integer in the range 0 - PHP_INT_MAX with large number being a higher priority.
     *                         The range between 100 and 100000 inclusively are reserved for application developer use
     *                         with everything outside that range reserved for internal use only.
     * @param bool   $watched  Flag to tell if file should be monitored for changes and updates or read initially and
     *                         future changes ignored. Note that the $force flag of update() can be used to override
     *                         this parameter.
     * @param array  $ignored  Any additional parameters given here are ignored. This allows sequences like
     *                         removeConfigFile(), change something, addConfigFile() to work without any fiddling to
     *                         remove any of the extra array elements like 'instant' or 'timestamp'.
     *
     * @return array Return the added config file candidate entry with 'priority', 'watched', and zero or more undefined
     *               additional array elements.
     * @throws \BadMethodCallException Throws exception if path file isn't set. False positive since config file name
     *                                 is always given.
     */
    public function addConfigFile(
        string $pathFile,
        int $priority = self::PRIORITY_DEFAULT,
        bool $watched = true,
        ...$ignored
    ): array {
        clearstatcache(true, $pathFile);
        $this->configFiles[$pathFile] = [
            'pathFile' => $pathFile,
            'priority' => $priority,
            'watched' => $watched,
            'instance' => (new YamlConfigFile($pathFile))->read(),
            'timestamp' => filemtime($pathFile)
        ];
        return $this->configFiles[$pathFile];
    }
    /**
     * Setter to allow managing settings that come from non-config file sources.
     *
     * This is mostly here because it is needed internal to solve some issues but might be useful to application
     * developers in some cases as well.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self Fluent interface.
     */
    public function addSetting(string $name, $value): self
    {
        $this->settings[$name] = $value;
        return $this;
    }
    /**
     * Will check watched config file for newer modification time and will update internal cached copy if it is newer.
     *
     * @param string $pathFile Configuration file name with absolute path.
     * @param bool   $force    Override watched flag. Allows checking of normally unwatched files.
     *
     * @return bool Returns true if config file was updated, else false.
     * @throws \BadMethodCallException Throws exception if path file isn't set. False positive since config file name
     *                                 is always given.
     * @throws \InvalidArgumentException Throws exception if config file is unknown.
     */
    public function checkModifiedAndUpdate(string $pathFile, bool $force = false): bool
    {
        if (!$this->hasConfigFile($pathFile)) {
            $mess = 'Tried to check unknown config file ' . $pathFile;
            throw new \InvalidArgumentException($mess);
        }
        $configFile = $this->configFiles[$pathFile];
        /**
         * @var ConfigFileInterface $instance
         */
        if ($force || $configFile['watched']) {
            clearstatcache(true, $pathFile);
            $currentTS = filemtime($pathFile);
            if ($configFile['timestamp'] < $currentTS) {
                $instance = $configFile['instance'];
                $instance->read();
                $configFile['timestamp'] = $currentTS;
                return true;
            }
        }
        return false;
    }
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
     * $manager = new ConfigManager($dic);
     * $configFiles = [
     *     __DIR__ . '/yapealDefaults.yaml',
     *     dirname(__DIR__, 2) . '/config/yapeal.yaml'
     * ];
     * $manager->create($configFiles);
     * ...
     * </code>
     *
     * An example that includes optional priority and watched flags:
     * <code>>
     * <?php
     * ...
     * $manager = new ConfigManager($dic);
     * $configFiles = [
     *     ['pathFile' => __DIR__ . '/yapealDefaults.yaml', 'priority' => PHP_INT_MAX, 'watched' => false],
     *     ['pathFile' => dirname(__DIR__, 2) . '/config/yapeal.yaml', 'priority' => 10],
     *     ['pathFile' => __DIR__ . '/special/run.yaml']
     * ];
     * $manager->create($configFiles);
     * ...
     * </code>
     *
     * Including either 'priority' or 'watched' is optional and they will receive the same default value(s) as from
     * addConfigFile() if not given.
     *
     * @param array $configFiles A list of config file names with optional priority and watched flag. See example for
     *                           how to include them.
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function create(array $configFiles): bool
    {
        $this->configFiles = [];
        foreach ($configFiles as $value) {
            if (is_string($value)) {
                $value = ['pathFile' => $value];
            } elseif (is_array($value)) {
                if (!array_key_exists('pathFile', $value)) {
                    $mess = 'Config file pathFile in required';
                    throw new \InvalidArgumentException($mess);
                }
            } else {
                $mess = 'Config file element must be a string or an array but was given ' . gettype($value);
                throw new \InvalidArgumentException($mess);
            }
            $this->addConfigFile($value['pathFile'],
                $value['priority'] ?? self::PRIORITY_DEFAULT,
                $value['watched'] ?? true);
        }
        return $this->update();
    }
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
    public function delete(): bool
    {
        $this->removeAddedSettings();
        $this->configFiles = [];
        return true;
    }
    /**
     * Allows checking if a config file candidate has already been added.
     *
     * @param string $pathFile Configuration file name with path. Path _should_ be absolute but it is not checked.
     *
     * @return bool Returns true if candidate entry exist, false if unknown.
     */
    public function hasConfigFile(string $pathFile): bool
    {
        return array_key_exists($pathFile, $this->configFiles);
    }
    /**
     * The Read part of the CRUD interface.
     *
     * Since the Container where the settings are kept is one of the main shared objects inside Yapeal-ng this is mostly
     * redundant but used this method as a way to return only stuff added by the config files.
     *
     * @return array
     */
    public function read(): array
    {
        $settings = [];
        foreach ($this->addedSettings as $addition) {
            $settings[$addition] = $this->dic[$addition];
        }
        return $settings;
    }
    /**
     * Remove an existing config file candidate entry.
     *
     * This method is expected to be used with the update() method to change the config files used during the composing
     * of settings.
     *
     * @param string $pathFile Configuration file name with path. Path _should_ be absolute but it is not checked.
     *
     * @return array Return the removed config file candidate entry with 'priority' and 'watched'.
     * @see addConfigFile()
     * @throws \InvalidArgumentException Throw this exception if there is no matching entry found. Use hasConfigFile()
     *                                   to check if the candidate config file entry exists.
     */
    public function removeConfigFile(string $pathFile): array
    {
        $result = ['pathFile' => $pathFile];
        if ($this->hasConfigFile($pathFile)) {
            $result = $this->configFiles[$pathFile];
            unset($this->configFiles[$pathFile]);
        }
        return $result;
    }
    /**
     * Sets the Container instance to use and protects any pre-existing settings it contains from overwrite by us.
     *
     * @param ContainerInterface $value
     *
     * @return self Fluent interface.
     */
    public function setDic(ContainerInterface $value): self
    {
        $this->dic = $value;
        $this->protectedKeys = $value->keys();
        // Insure this class is a protected key.
        $this->protectedKeys[] = 'Yapeal.Configuration.Callable.Manager';
        // Insure main wiring class is protected key as well.
        $this->protectedKeys[] = 'Yapeal.Wiring.Callable.Wiring';
        $this->protectedKeys = array_unique($this->protectedKeys);
        return $this;
    }
    /**
     * Sets substitutions to require Yapeal prefix or to be more generic.
     *
     * @param bool $value
     *
     * @return self Fluent interface
     * @see doSubstitutions()
     */
    public function setMatchYapealOnly(bool $value = true): self
    {
        $this->matchYapealOnly = $value;
        return $this;
    }
    /**
     * The Update part of the CRUD interface.
     *
     * Note that the method expected that the addConfigFile() and removeConfigFile() methods have already been called
     * to change which config files will be used while composing the new settings.
     *
     * @param bool $force Override watched flag. Allows checking of normally unwatched files.
     *
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function update(bool $force = false): bool
    {
        $this->removeAddedSettings();
        $settings = $this->settings;
        $this->sortConfigFiles();
        /**
         * @var ConfigFileInterface $instance
         */
        foreach ($this->configFiles as $pathFile => $configFile) {
            $this->checkModifiedAndUpdate($pathFile, $force);
            $instance = $configFile['instance'];
            $settings = array_replace($settings, $instance->flattenYaml());
        }
        $this->doSubstitutions($settings);
        return true;
    }
    /**
     * Looks for and replaces any {Yapeal.*} it finds in values with the corresponding other setting value.
     *
     * This will replace full value or part of the value. Examples:
     *
     *     $settings = [
     *         'Yapeal.baseDir' => '/my/junk/path/Yapeal/',
     *         'Yapeal.libDir' => '{Yapeal.baseDir}lib/'
     *         'Yapeal.Sql.dir' => '{Yapeal.libDir}Sql/'
     *     ];
     *
     * After doSubstitutions would be:
     *
     *     $settings = [
     *         'Yapeal.baseDir' => '/my/junk/path/Yapeal/',
     *         'Yapeal.libDir' => '/my/junk/path/Yapeal/lib/'
     *         'Yapeal.Sql.dir' => '/my/junk/path/Yapeal/lib/Sql/'
     *     ];
     *
     * Note that order in which subs are done is undefined so it could have
     * done libDir first and then baseDir into both or done baseDir into libDir
     * then libDir into Sql.dir.
     *
     * Subs from within $settings itself are used first with $dic used to
     * fill-in as needed for any unknown ones.
     *
     * Subs are tried up to 25 times as long as any {Yapeal.*} are found before
     * giving up to prevent infinite loop.
     *
     * @param array $settings
     *
     * @throws \InvalidArgumentException
     */
    protected function doSubstitutions(array $settings)
    {
        $additions = array_diff(array_keys($settings), $this->protectedKeys);
        $depth = 0;
        $maxDepth = 25;
        $regEx = sprintf('#(.*?)\{((?:%s)(?:\.\w+)+)\}(.*)#', $this->matchYapealOnly ? 'Yapeal' : '\w+');
        do {
            $miss = 0;
            foreach ($additions as $addition) {
                if (!is_string($settings[$addition])) {
                    continue;
                }
                $matched = preg_match($regEx, $settings[$addition], $matches);
                if (1 === $matched) {
                    $sub = $this->dic[$matches[2]] ?? $settings[$matches[2]] ?? $matches[2];
                    $settings[$addition] = $matches[1] . $sub . $matches[3];
                    if (fnmatch('*{*.*}*', $settings[$addition])) {
                        ++$miss;
                    }
                } elseif (false === $matched) {
                    $mess = sprintf('You have received an unicorn in the form of a preg error %s. Please report it',
                        preg_last_error());
                    throw new \InvalidArgumentException($mess);
                }
            }
            if (++$depth > $maxDepth) {
                $mess = 'Exceeded maximum depth, check for possible circular reference(s)';
                throw new \InvalidArgumentException($mess);
            }
        } while (0 < $miss);
        foreach ($additions as $add) {
            $this->dic[$add] = $settings[$add];
        }
        $this->addedSettings = $additions;
    }
    /**
     * Used to remove any parameters from the Container that were added before from config files.
     *
     * Note that anything that was in $this->settings during the last create() or update() will also be removed.
     */
    private function removeAddedSettings()
    {
        foreach ($this->addedSettings as $sub) {
            unset($this->dic[$sub]);
        }
        $this->addedSettings = [];
    }
    /**
     * Sorts config files by priority/path file order.
     *
     * Sorted the config files by their descending priority order (largest-smallest). If there are config files with
     * equal priorities they will be sorted by descending path file order.
     */
    private function sortConfigFiles()
    {
        uasort($this->configFiles,
            function ($a, $b) {
                $sort = $b['priority'] <=> $a['priority'];
                if (0 === $sort) {
                    $sort = $b['pathFile'] <=> $a['pathFile'];
                }
                return $sort;
            });
    }
    /**
     * @var array $addedSettings
     */
    private $addedSettings;
    /**
     * @var array $configFiles
     */
    private $configFiles;
    /**
     * @var ContainerInterface $dic
     */
    private $dic;
    /**
     * Flag used while doing substitutions to decide if generic pattern or Yapeal prefixed one should be used.
     *
     * @var bool $matchYapealOnly
     * @see doSubstitutions()
     */
    private $matchYapealOnly;
    /**
     * List of Container keys that are protected from being overwritten.
     *
     * @var array $protectedKeys
     */
    private $protectedKeys;
    /**
     * @var array $settings
     */
    private $settings;
}
