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
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use Yapeal\Configuration\ConfigFileInterface;
use Yapeal\Configuration\ConfigManagementInterface;
use Yapeal\Configuration\ConfigManager;
use Yapeal\Container\Container;
use Yapeal\Container\ContainerInterface;

/**
 * Class ConfigManagerContext.
 */
class ConfigManagerContext implements Context
{
    use FileSystemUtilTrait;
    /**
     * ConfigManagerContext constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->configFiles = [];
    }
    public function __destruct()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @rmdir(dirname($this->workingDirectory));
    }
    /**
     * @AfterScenario
     */
    public function afterScenarioClearConfigFiles()
    {
        $this->configFiles = [];
    }
    /**
     * @When I give the path name :pathName parameter to the addConfigFile method
     *
     * @param string $pathName
     *
     * @throws \InvalidArgumentException
     */
    public function iGiveThePathNameParameterToTheAddConfigFileMethod(string $pathName)
    {
        $result = $this->manager->addConfigFile($this->workingDirectory . $pathName);
        Assert::isArray($result);
        Assert::keyExists($result, 'instance');
        Assert::isInstanceOf($result['instance'], ConfigFileInterface::class);
        Assert::keyExists($result, 'timestamp');
        Assert::keyExists($result, 'priority');
        Assert::keyExists($result, 'watched');
    }
    /**
     * @Given I have a config file :pathFile that contains:
     * @Given I had a config file :pathFile that contained:
     * @Given I have another config file :pathFile that contains:
     *
     * @param string       $pathFile
     * @param PyStringNode $contents
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function iHaveAConfigFileThatContains(string $pathFile, PyStringNode $contents)
    {
        $this->filesystem->dumpFile($this->workingDirectory . $pathFile, (string)$contents);
        $this->configFiles[] = $this->workingDirectory . $pathFile;
    }
    /**
     * @Given I have an empty Container class
     */
    public function iHaveAnEmptyContainerClass()
    {
        $this->dic = new Container();
        $this->dic['protect.me'] = 'Was I protected?';
    }
    /**
     * @Given I have created a new instance of the ConfigManager class
     */
    public function iHaveCreatedANewInstanceOfTheConfigManagerClass()
    {
        $this->manager = new ConfigManager($this->dic);
    }
    /**
     * @Then  I should find the follows <keys> in the Container class:
     * @Given I could find the follows <keys> in the Container class:
     *
     * @param TableNode $table
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \InvalidArgumentException
     */
    public function iShouldFindTheFollowsKeysInTheContainerClass(TableNode $table)
    {
        $expected = $table->getColumn(0);
        array_shift($expected);
        $keys = $this->dic->keys();
        foreach ($expected as $item) {
            if (!in_array($item, $keys)) {
                $mess = sprintf('Expected the key %s to exist.', $item);
                throw new \InvalidArgumentException($mess);
            }
        }
    }
    /**
     * @Then I should not find the follows <keys> in the Container class:
     *
     * @param TableNode $table
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \InvalidArgumentException
     */
    public function iShouldNotFindTheFollowsKeysInTheContainerClass(TableNode $table)
    {
        $notExpected = $table->getColumn(0);
        array_shift($notExpected);
        $keys = $this->dic->keys();
        foreach ($notExpected as $item) {
            if (in_array($item, $keys)) {
                $mess = sprintf('Do not expected the key %s to exist.', $item);
                throw new \InvalidArgumentException($mess);
            }
        }
    }
    /**
     * @When  I use the create method of the ConfigManager class
     * @Given I used the create method of the ConfigManager class
     * @throws \InvalidArgumentException
     */
    public function iUseTheCreateMethodOfTheConfigManagerClass()
    {
        Assert::true($this->manager->create($this->configFiles));
        $this->configFiles = [];
    }
    /**
     * @When I use the delete method of the ConfigManager class
     * @throws \InvalidArgumentException
     */
    public function iUseTheDeleteMethodOfTheConfigManagerClass()
    {
        Assert::true($this->manager->delete());
    }
    /**
     * @When I use the update method of the ConfigManager class
     * @throws \InvalidArgumentException
     */
    public function iUseTheUpdateMethodOfTheConfigManagerClass()
    {
        Assert::true($this->manager->update());
    }
    /**
     * @var array $configFiles
     */
    private $configFiles;
    /**
     * @var ContainerInterface $dic
     */
    private $dic;
    /**
     * @var ConfigManagementInterface $manager
     */
    private $manager;
}
