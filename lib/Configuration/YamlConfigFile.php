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
     * @param array $settings Contents as an associate array.
     */
    public function __construct(string $pathFile = null, array $settings = [])
    {
        $this->pathFile = $pathFile;
        $this->settings = $settings;
    }
    /**
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
     * @return string
     * @throws \LogicException
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
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
    /**
     * @return self Fluent interface.
     * @throws \LogicException
     */
    public function read(): self
    {
        $data = $this->safeFileRead($this->getPathFile());
        if (false === $data) {
            $this->setSettings([]);
            return $this;
        }
        try {
            $data = (new Parser())->parse($data, true, false);
        } catch (ParseException $exc) {
            $data = [];
        }
        $this->setSettings($data);
        return $this;
    }
    /**
     * @throws \LogicException
     */
    public function save()
    {
        $data = (new Dumper())->dump($this->getSettings());
        $this->safeDataWrite($this->getPathFile(), $data);
    }
    /**
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
     * @param array $value
     *
     * @return self Fluent interface.
     */
    public function setSettings(array $value = []): self
    {
        $this->settings = $value;
        return $this;
    }
    /**
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
        foreach ((array)$yaml as $key => $value) {
            $this->arraySet($output, $key, $value);
            if (is_array($value) && false !== strpos($key, '.')) {
                $nested = $this->unflattenYaml($value);
                $output[$key] = $nested;
            }
        }
        return $output;
    }
    /**
     * @param array      $array
     * @param string|int $key
     * @param mixed      $value
     *
     * @return array
     */
    private function arraySet(array &$array, $key, $value): array
    {
        if (null === $key) {
            $array = $value;
            return $array;
        }
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
     * @var string $pathFile
     */
    private $pathFile;
    /**
     * @var array $settings
     */
    private $settings;
}
