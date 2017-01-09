<?php
declare(strict_types = 1);
/**
 * Contains class YamlConfigFile.
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

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Yapeal\FileSystem\SafeFileHandlingTrait;

/**
 * Class YamlConfigFile.
 */
class YamlConfigFile implements ConfigFileInterface
{
    use SafeFileHandlingTrait;
    /**
     * YamlConfigFile constructor.
     *
     * @param string|null $pathFile File name with absolute path.
     * @param array       $settings Contents as an associate array.
     */
    public function __construct(string $pathFile = null, array $settings = [])
    {
        $this->pathFile = $pathFile;
        $this->settings = $settings;
    }
    /**
     * Flatten array to a single dimension where the new key contains the original keys joined together by a '.'.
     *
     * @param array|null $yaml The array to be flattened. If null assumes $settings.
     *
     * @return array
     */
    public function flattenYaml(array $yaml = null): array
    {
        if (null === $yaml) {
            $yaml = $this->getSettings();
        }
        if (0 === count($yaml)) {
            return [];
        }
        $rItIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($yaml));
        $settings = [];
        foreach ($rItIt as $leafValue) {
            $keys = [];
            foreach (range(0, $rItIt->getDepth()) as $depth) {
                $keys[] = $rItIt->getSubIterator($depth)
                    ->key();
            }
            $settings[implode('.', $keys)] = $leafValue;
        }
        return $settings;
    }
    /**
     * Getter for path file.
     *
     * @return string File name with absolute path.
     * @throws \LogicException Throws exception if path file isn't set.
     */
    public function getPathFile(): string
    {
        if (null === $this->pathFile) {
            $mess = 'Trying to access $pathFile before it was set';
            throw new \LogicException($mess);
        }
        return $this->pathFile;
    }
    /**
     * Getter for complete list of settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
    /**
     * Used to read data from the config file.
     *
     * @return YamlConfigFile Fluent interface.
     * @throws ParseException Throws exception if there is a problem with yaml file.
     * @throws \BadMethodCallException Throws exception if path file isn't set.
     */
    public function read(): self
    {
        try {
            $data = $this->safeFileRead($this->getPathFile());
        } catch (\LogicException $exc) {
            $mess = 'Path file must be set before trying to read config file';
            throw new \BadMethodCallException($mess, 1, $exc);
        }
        if (false === $data) {
            $this->setSettings([]);
            return $this;
        }
        $data = (new Parser())->parse($data, true, false);
        $this->setSettings($data);
        return $this;
    }
    /**
     * Used to save data to config file.
     *
     * @throws \BadMethodCallException Throws exception if path file isn't set.
     */
    public function save()
    {
        $data = "---\n" . (new Dumper())->dump($this->getSettings(), 9) . "...";
        try {
            $this->safeDataWrite($this->getPathFile(), $data);
        } catch (\LogicException $exc) {
            $mess = 'Path file must be set before trying to save config file';
            throw new \BadMethodCallException($mess, 1, $exc);
        }
    }
    /**
     * Used to set or reset the config file path name.
     *
     * @param string|null $value File name with absolute path.
     *
     * @return self Fluent interface.
     */
    public function setPathFile(string $value = null): self
    {
        $this->pathFile = $value;
        return $this;
    }
    /**
     * Used to give settings in mass.
     *
     * @param array $value A multi-dimensional assoc array.
     *
     * @return self Fluent interface.
     */
    public function setSettings(array $value = []): self
    {
        $this->settings = $value;
        return $this;
    }
    /**
     * Expands any keys containing '.' into a multi-dimensional assoc array and their values.
     *
     * @param array|null $yaml The array to be unflattened. If null assumes $settings.
     *
     * @return array
     */
    public function unflattenYaml(array $yaml = null): array
    {
        if (null === $yaml) {
            $yaml = $this->getSettings();
        }
        if (0 === count($yaml)) {
            return [];
        }
        $output = [];
        foreach ($yaml as $key => $value) {
            $this->arraySet($output, $key, $value);
            if (is_array($value) && false !== strpos($key, '.')) {
                $nested = $this->unflattenYaml($value);
                $output[$key] = $nested;
            }
        }
        return $output;
    }
    /**
     * Used by unflattenYaml() to expand keys.
     *
     * @param array      $array
     * @param string|int $key
     * @param mixed      $value
     *
     * @return array
     */
    private function arraySet(array &$array, $key, $value): array
    {
        if (is_int($key)) {
            $key = (string)$key;
        }
        $keys = explode('.', $key);
        while (1 < count($keys)) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!array_key_exists($key, $array) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }
    /**
     * @var string $pathFile Holds Yaml config file name with absolute path.
     */
    private $pathFile;
    /**
     * @var array $settings Holds the multi-dimensional assoc array either from the Yaml file or to be saved to it.
     */
    private $settings;
}
