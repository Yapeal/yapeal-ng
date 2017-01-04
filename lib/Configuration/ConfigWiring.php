<?php
declare(strict_types = 1);
/**
 * Contains class ConfigWiring.
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

use Yapeal\Container\ContainerInterface;

/**
 * Class ConfigWiring.
 */
class ConfigWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function wire(ContainerInterface $dic)
    {
        $this->wireYaml($dic)
            ->wireManager($dic)
            ->wireExtractorCallable($dic);
        $path = dirname(str_replace('\\', '/', __DIR__), 2) . '/';
        // These two paths are critical to Yapeal-ng working and can't be overridden.
        $dic['Yapeal.baseDir'] = $path;
        $dic['Yapeal.libDir'] = $path . 'lib/';
        $settings = $this->gitVersionSetting($dic, []);
        $configFiles = [
            [
                'pathName' => str_replace('\\', '/', __DIR__) . '/yapealDefaults.yaml',
                PHP_INT_MAX,
                false
            ]
        ];
        /**
         * Do to the importance that the cache/ and log/ directories and the main configuration file _not_ point to
         * somewhere under Composer's vendor/ directory they are now forced to use either vendor parent directory or
         * Yapeal-ng's base directory depending on if Yapeal-ng finds itself under a vendor/ directory.
         *
         * If as an application developer you wish to use a different directory than cache/ or log/ in your
         * application's root directory you __MUST__ set the 'Yapeal.FileSystem.Cache.dir' and/or 'Yapeal.Log.dir'
         * setting(s) of the Container before you give it to the Wiring class to prevent them being changed here when
         * Wiring::wireAll() calls this class method during wireAll()'s normal processing. On the command line you have
         * the -c or --configFile option to override the settings as well.
         *
         * Read the existing [Configuration Files](../../docs/config/ConfigurationFiles.md) docs for more about how to
         * override the optional config/yapeal.yaml file as well.
         */
        if (false !== $vendorPos = strpos($path, 'vendor/')) {
            $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
            $configFiles[] = $dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml';
            $settings['Yapeal.FileSystem.Cache.dir'] = '{Yapeal.vendorParentDir}cache/';
            $settings['Yapeal.Log.dir'] = '{Yapeal.vendorParentDir}log/';
        } else {
            $configFiles[] = $path . 'config/yapeal.yaml';
            $settings['Yapeal.FileSystem.Cache.dir'] = '{Yapeal.baseDir}cache/';
            $settings['Yapeal.Log.dir'] = '{Yapeal.baseDir}log/';
        }
        /**
         * @var ConfigManagementInterface $manager
         */
        $manager = $dic['Yapeal.Configuration.Callable.Manager'];
        $manager->setSettings($settings)
            ->create($configFiles);
    }
    /**
     * Writes git version to Container if found else default setting that can be overridden latter in config file.
     *
     * @param ContainerInterface $dic
     * @param array              $settings
     *
     * @return array
     */
    private function gitVersionSetting(ContainerInterface $dic, array $settings): array
    {
        $gitVersion = trim(exec('git describe --always --long 2>&1', $junk, $status));
        if (0 === $status && '' !== $gitVersion) {
            $dic['Yapeal.version'] = $gitVersion . '-dev';
        } else {
            $settings['Yapeal.version'] = '0.0.0-0-noGit-unknown';
        }
        return $settings;
    }
    /**
     * Adds a protected function that will extract all scalar settings that share a common prefix.
     *
     * The prefix should end with a '.' if not the function will end one before starting search.
     *
     * @param ContainerInterface $dic
     *
     * @return ConfigWiring Fluent interface.
     * @throws \InvalidArgumentException Could only be thrown if Container or PHP were broken.
     */
    private function wireExtractorCallable(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Config.Callable.ExtractScalarsByKeyPrefix'])) {
            $dic['Yapeal.Config.Callable.ExtractScalarsByKeyPrefix'] = $dic->protect(function (
                ContainerInterface $dic,
                string $prefix
            ) {
                $preLen = strlen($prefix);
                if ($preLen !== strrpos($prefix, '.') + 1) {
                    $prefix .= '.';
                    ++$preLen;
                }
                try {
                    foreach ($dic->keys() as $key) {
                        $lastDotPlusOne = strrpos($key, '.') + 1;
                        if (0 === strpos($key, $prefix) && $preLen === $lastDotPlusOne && is_scalar($dic[$key])) {
                            $name = substr($key, $lastDotPlusOne);
                            yield $name => $dic[$key];
                        }
                    }
                } catch (\InvalidArgumentException $exc) {
                    // Only way this can happen is if the Container or PHP are broken.
                    $mess = 'Container or PHP are fatally broken;'
                        . ' Received InvalidArgumentException when accessing setting in Container with known good key';
                    trigger_error($mess, E_USER_ERROR);
                    exit(254);
                }
            });
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireManager(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Configuration.Callable.Manager'])) {
            $dic['Yapeal.Configuration.Callable.Manager'] = function (ContainerInterface $dic) {
                /**
                 * @var ConfigManager $manager
                 */
                $manager = new $dic['Yapeal.Configuration.Classes.manager']($dic);
                return $manager->setMatchYapealOnly($dic['Yapeal.Configuration.Parameters.manager.matchYapealOnly']);
            };
        }
        return $this;
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     */
    private function wireYaml(ContainerInterface $dic): self
    {
        if (empty($dic['Yapeal.Config.Callable.Yaml'])) {
            $dic['Yapeal.Config.Callable.Yaml'] = $dic->factory(function (ContainerInterface $dic) {
                return new $dic['Yapeal.Configuration.Classes.yaml']();
            });
        }
        return $this;
    }
}
