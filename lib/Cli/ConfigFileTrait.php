<?php
declare(strict_types = 1);
/**
 * Contains trait ConfigFileTrait.
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
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Cli;

use Symfony\Component\Console\Input\InputOption;
use Yapeal\Configuration\ConfigManagementInterface;
use Yapeal\Container\ContainerInterface;

/**
 * Trait ConfigFileTrait.
 */
trait ConfigFileTrait
{
    /**
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function addConfigFileOption()
    {
        $mess = 'Configuration file to get settings from.'
            . ' <comment>NOTE: A (missing, unreadable, empty, etc) file will be silently ignored.</comment>';
        $this->addOption('configFile', 'c', InputOption::VALUE_REQUIRED, $mess);
    }
    /**
     * Process the configuration file gotten on the CLI.
     *
     * NOTE: All settings from this configuration file overwrite any existing values.
     *
     * @param string             $pathFile Path and file name of config file to
     *                                     be processed.
     * @param ContainerInterface $dic      Instance of Container that will be
     *                                     updated.
     *
     * @throws \DomainException
     * @throws \LogicException
     */
    protected function processConfigFile(string $pathFile, ContainerInterface $dic)
    {
        $pathFile = trim($pathFile);
        if ('' === $pathFile) {
            return;
        }
        /**
         * @var ConfigManagementInterface $manager
         */
        $manager = $dic['Yapeal.Configuration.Callable.Manager'];
        $manager->addConfigFile($pathFile, 1);
        $manager->update();
    }
}
