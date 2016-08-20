<?php
declare(strict_types = 1);
/**
 * Contains Wiring class.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use FilePathNormalizer\FilePathNormalizerTrait;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealException;

/**
 * Class Wiring
 */
class Wiring
{
    use FilePathNormalizerTrait;
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->dic = $dic;
    }
    /**
     * @return ContainerInterface
     */
    public function getDic(): ContainerInterface
    {
        return $this->dic;
    }
    /**
     * @param string $configFile
     * @param array  $existing
     *
     * @return array
     * @throws \DomainException
     * @throws \Yapeal\Exception\YapealException
     */
    public function parserConfigFile(string $configFile, array $existing = []): array
    {
        if (!is_readable($configFile) || !is_file($configFile)) {
            return $existing;
        }
        try {
            /**
             * @var \RecursiveIteratorIterator|\Traversable $rItIt
             */
            $rItIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator((new Parser())->parse(file_get_contents($configFile),
                true,
                false)));
        } catch (ParseException $exc) {
            $mess = sprintf('Unable to parse the YAML configuration file %2$s. The error message was %1$s',
                $exc->getMessage(),
                $configFile);
            throw new YapealException($mess, 0, $exc);
        }
        $settings = [];
        foreach ($rItIt as $leafValue) {
            $keys = [];
            foreach (range(0, $rItIt->getDepth()) as $depth) {
                $keys[] = $rItIt->getSubIterator($depth)
                    ->key();
            }
            $settings[implode('.', $keys)] = $leafValue;
        }
        return array_replace($existing, $settings);
    }
    /**
     * @return self Fluent interface.
     * @throws \LogicException
     */
    public function wireAll()
    {
        $dic = $this->dic;
        $base = 'Yapeal.Wiring.Handlers.';
        $names = ['Config', 'Error', 'Event', 'Log', 'Sql', 'Xml', 'Xsd', 'Xsl', 'FileSystem', 'Network', 'EveApi'];
        /**
         * @var WiringInterface $class
         */
        foreach ($names as $name) {
            $setting = $base . strtolower($name);
            if (!empty($dic[$setting])
                && is_subclass_of($dic[$setting], '\\Yapeal\\Configuration\\WiringInterface', true)
            ) {
                $class = new $dic[$setting];
                $class->wire($dic);
                continue;
            }
            $methodName = 'wire' . $name;
            if (method_exists($this, $methodName)) {
                $this->$methodName();
            } else {
                $mess = 'Could NOT find class or method for ' . $name;
                throw new \LogicException($mess);
            }
        }
        return $this;
    }
    /**
     * @param array $settings
     *
     * @return array
     * @throws \DomainException
     */
    protected function doSubs(array $settings): array
    {
        if (0 === count($settings)) {
            return [];
        }
        $depth = 0;
        $maxDepth = 10;
        $regEx = '%(?<all>\{(?<name>Yapeal(?:\.\w+)+)\})%';
        $dic = $this->dic;
        do {
            $settings = preg_replace_callback($regEx,
                function ($match) use ($settings, $dic) {
                    if (array_key_exists($match['name'], $settings)) {
                        return $settings[$match['name']];
                    }
                    if (!empty($dic[$match['name']])) {
                        return $dic[$match['name']];
                    }
                    return $match['all'];
                },
                $settings,
                -1,
                $count);
            if (++$depth > $maxDepth) {
                $mess = 'Exceeded maximum depth, check for possible circular reference(s)';
                throw new \DomainException($mess);
            }
            $lastError = preg_last_error();
            if (PREG_NO_ERROR !== $lastError) {
                $constants = array_flip(get_defined_constants(true)['pcre']);
                $lastError = $constants[$lastError];
                $mess = 'Received preg error ' . $lastError;
                throw new \DomainException($mess);
            }
        } while ($count > 0);
        return $settings;
    }
    /**
     * @return void
     * @throws \DomainException
     * @throws \Yapeal\Exception\YapealException
     */
    protected function wireConfig()
    {
        $dic = $this->dic;
        $fpn = $this->getFpn();
        $path = $fpn->normalizePath(dirname(dirname(__DIR__)));
        if (empty($dic['Yapeal.baseDir'])) {
            $dic['Yapeal.baseDir'] = $path;
        }
        if (empty($dic['Yapeal.libDir'])) {
            $dic['Yapeal.libDir'] = $path . 'lib/';
        }
        $configFiles = [
            $fpn->normalizeFile(__DIR__ . '/yapeal_defaults.yaml'),
            $fpn->normalizeFile($dic['Yapeal.baseDir'] . 'config/yapeal.yaml')
        ];
        $vendorPos = strpos($path, 'vendor/');
        if (false !== $vendorPos) {
            $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            $configFiles[] = $fpn->normalizeFile($dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml');
        }
        $settings = [];
        // Process each file in turn so any substitutions are done in a more
        // consistent way.
        foreach ($configFiles as $configFile) {
            $settings = $this->parserConfigFile($configFile, $settings);
        }
        $settings = $this->doSubs($settings);
        if (0 !== count($settings)) {
            // Assure NOT overwriting already existing settings given by application.
            foreach ($settings as $key => $value) {
                $dic[$key] = $dic[$key] ?? $value;
            }
        }
    }
    /**
     * @var ContainerInterface $dic
     */
    protected $dic;
}
