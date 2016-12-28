<?php
declare(strict_types = 1);
/**
 * Contains class ConfigManagerContext.
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
namespace Yapeal\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Yapeal\Configuration\ConfigManagementInterface;
use Yapeal\Configuration\ConfigManager;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;

/**
 * Class ConfigManagerContext.
 */
class ConfigManagerContext implements Context
{
    /**
     * @Given I have a config file :arg1 that contains:
     */
    public function iHaveAConfigFileThatContains($arg1, PyStringNode $string)
    {
        throw new PendingException();
    }
    /**
     * @Given I have an empty Container class
     */
    public function iHaveAnEmptyContainerClass()
    {
        $this->dic = new Container();
    }
    /**
     * @Given I have created a new instance of the ConfigManager class
     */
    public function iHaveCreatedANewInstanceOfTheConfigManagerClass()
    {
        $this->manager = new ConfigManager($this->dic);
    }
    /**
     * @Then I should can find the follows <keys> and their <values> in the Container class:
     */
    public function iShouldCanFindTheFollowsKeysAndTheirValuesInTheContainerClass(TableNode $table)
    {
        throw new PendingException();
    }
    /**
     * @When I use the create() method of the ConfigManager class
     */
    public function iUseTheCreateMethodOfTheConfigManagerClass()
    {
        throw new PendingException();
    }
    /**
     * @var ContainerInterface $dic
     */
    private $dic;
    /**
     * @var ConfigManagementInterface $manager
     */
    private $manager;
}
