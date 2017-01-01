<?php
declare(strict_types = 1);
/**
 * Contains trait ConfigFileProcessingTrait.
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

use Yapeal\Cli\Yapeal\ConfigFileInterface;
use Yapeal\Cli\Yapeal\YamlConfigFile;
use Yapeal\Container\ContainerInterface;
use Yapeal\FileSystem\SafeFileHandlingTrait;

/**
 * Trait ConfigFileProcessingTrait.
 */
trait ConfigFileProcessingTrait
{
    use SafeFileHandlingTrait;
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
     * @param array              $settings
     * @param ContainerInterface $dic
     *
     * @return array
     * @throws \DomainException
     */
    protected function doSubstitutions(array $settings, ContainerInterface $dic): array
    {
        if (0 === count($settings)) {
            return [];
        }
        $depth = 0;
        $maxDepth = 25;
        $callback = function ($subject) use ($dic, $settings, &$miss) {
            $regEx = '%(.*?)\{((?:\w+)(?:\.\w+)+)\}(.*)%';
            if (is_string($subject)) {
                $matched = preg_match($regEx, $subject, $matches);
                if (1 === $matched) {
                    $name = $matches[2];
                    if ($dic->offsetExists($name)) {
                        $subject = $matches[1] . $dic[$name] . $matches[3];
                    } elseif (array_key_exists($name, $settings)) {
                        $subject = $matches[1] . $settings[$name] . $matches[3];
                    }
                    if (false !== strpos($subject, '{') && false !== strpos($subject, '}')) {
                        ++$miss;
                    }
                } elseif (false === $matched) {
                    $constants = array_flip(array_filter(get_defined_constants(),
                        function (string $value) {
                            return fnmatch('PREG_*_ERROR', $value);
                        },
                        ARRAY_FILTER_USE_KEY));
                    $mess = 'Received preg error ' . $constants[preg_last_error()];
                    throw new \DomainException($mess);
                }
            }
            return $subject;
        };
        do {
            $miss = 0;
            $settings = array_map($callback, $settings);
            if (++$depth > $maxDepth) {
                $mess = 'Exceeded maximum depth, check for possible circular reference(s)';
                throw new \DomainException($mess);
            }
        } while (0 < $miss);
        return $settings;
    }
    /**
     * Converts any depth Yaml config file into a flattened array with '.' separators and values.
     *
     * @param string $configFile
     * @param array  $existing
     *
     * @return array
     * @throws \LogicException
     * @throws \DomainException
     */
    protected function parserConfigFile(string $configFile, array $existing = []): array
    {
        /**
         * @var ConfigFileInterface $yaml
         */
        $yaml = $this->getDic()['Yapeal.Config.Callable.Yaml'];
        $settings = $yaml->setPathFile($configFile)
            ->read()
            ->flattenYaml();
        return array_replace($existing, $settings);
    }
}
