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

use Yapeal\Cli\Yapeal\YamlConfigFile;
use Yapeal\Container\ContainerInterface;
use Yapeal\DicAwareInterface;
use Yapeal\DicAwareTrait;

/**
 * Class Wiring
 */
class Wiring implements DicAwareInterface
{
    use ConfigFileProcessingTrait, DicAwareTrait;
    /**
     * @param ContainerInterface $dic
     */
    public function __construct(ContainerInterface $dic)
    {
        $this->setDic($dic);
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
     * @return void
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function wireConfig()
    {
        $dic = $this->getDic();
        $path = str_replace('\\', '/', dirname(dirname(__DIR__))) . '/';
        // These two paths are critical to Yapeal-ng working and can't be overridden here.
        $dic['Yapeal.baseDir'] = $path;
        $dic['Yapeal.libDir'] = $path . 'lib/';
        if (empty($dic['Yapeal.Config.Yaml'])) {
            $dic['Yapeal.Config.Yaml'] = $dic->factory(function () {
                return new YamlConfigFile();
            });
        }
        $configFiles = [
            __DIR__ . '/yapeal_defaults.yaml',
            $path . 'config/yapeal.yaml'
        ];
        $vendorPos = strpos($path, 'vendor/');
        if (false !== $vendorPos) {
            $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            $configFiles[] = $dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml';
        }
        $settings = [];
        // Process each file in turn so any substitutions are done in a more
        // consistent way.
        foreach ($configFiles as $configFile) {
            $settings = $this->parserConfigFile($configFile, $settings);
        }
        $settings = $this->doSubstitutions($settings, $dic);
        if (0 !== count($settings)) {
            // Assure NOT overwriting already existing settings given by application.
            foreach ($settings as $key => $value) {
                $dic[$key] = $dic[$key] ?? $value;
            }
        }
    }
}
