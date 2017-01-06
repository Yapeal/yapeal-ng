<?php
declare(strict_types = 1);
/**
 * Contains class ConfigManagerSpec.
 *
 * PHP version 7.0
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
 * @license   LGPL-3.0
 */
namespace Spec\Yapeal\Configuration;

use PhpSpec\ObjectBehavior;
use Spec\Yapeal\FileSystemUtilTrait;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use Yapeal\Container\Container;

/**
 * Class ConfigManagerSpec
 *
 * @mixin TestConfigManager
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class ConfigManagerSpec extends ObjectBehavior
{
    use FileSystemUtilTrait;
    /**
     * TransformerSpec constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
    public function __destruct()
    {
        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @rmdir(dirname($this->workingDirectory));
    }
    public function it_does_not_allow_overwrite_preexisting_container_settings_in_create()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
    Protect:
        me: 'not protected'
...
yaml;
        $dic = new Container(['Yapeal.Protect.me' => '{Yapeal.version}']);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic);
        $this->create([$configFile]);
        Assert::eq($dic['Yapeal.Protect.me'], '{Yapeal.version}');
    }
    public function it_does_not_allow_self_to_be_overwritten_in_create()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    Configuration:
        Callable:
            Manager: 'not protected'
...
yaml;
        $dic = new Container();
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic);
        $dic['Yapeal.Configuration.Callable.Manager'] = 'protected';
        $this->create([$configFile]);
        Assert::notEq($dic['Yapeal.Configuration.Callable.Manager'], 'not protected');
    }
    public function it_does_not_do_subs_for_preexisting_container_settings_in_create()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container(['Yapeal.Test.version' => '{Yapeal.version}']);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->create([$configFile]);
        Assert::eq($dic['Yapeal.Test.version'], '{Yapeal.version}');
    }
    public function it_does_subs_in_create()
    {
        $yaml = <<<'yaml'
---
MyApp:
    version: '0.0.1-0-g123456'
Yapeal:
    meTo: '{MyApp.version}'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $settings = ['Yapeal.Test.version' => '{Yapeal.version}'];
        $this->beConstructedWith($dic, $settings);
        $this->create([$configFile]);
        Assert::eq($dic['Yapeal.Test.version'], $dic['Yapeal.version']);
        Assert::eq($dic['Yapeal.meTo'], '0.0.1-0-g123456');
    }
    public function it_ignores_other_subs_when_match_yapeal_only_is_true_in_create()
    {
        $yaml = <<<'yaml'
---
MyApp:
    version: '0.0.1-0-g123456'
Yapeal:
    ignored: '{MyApp.version}'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $settings = ['Yapeal.Test.version' => '{Yapeal.version}'];
        $this->beConstructedWith($dic, $settings);
        $this->setMatchYapealOnly()
            ->create([$configFile]);
        Assert::eq($dic['Yapeal.Test.version'], $dic['Yapeal.version']);
        Assert::eq($dic['Yapeal.ignored'], '{MyApp.version}');
    }
    public function it_processes_config_files_by_priority_order_in_create()
    {
        $dic = new Container();
        $yaml = <<<'yaml'
---
Yapeal:
    config: 'priority second'
...
yaml;
        $configFile2 = $this->workingDirectory . 'config2.yaml';
        $this->filesystem->dumpFile($configFile2, $yaml);
        $yaml = <<<'yaml'
---
Yapeal:
    config: 'priority first'
...
yaml;
        $configFile1 = $this->workingDirectory . 'config1.yaml';
        $this->filesystem->dumpFile($configFile1, $yaml);
        $this->beConstructedWith($dic);
        $configFiles = [
            ['pathName' => $configFile2, 'priority' => PHP_INT_MAX - 1],
            ['pathName' => $configFile1, 'priority' => PHP_INT_MAX]
        ];
        $this->create($configFiles);
        Assert::eq($dic['Yapeal.config'], 'priority second');
    }
    public function it_processes_config_files_with_equal_priority_by_path_name_order_in_create()
    {
        $dic = new Container();
        $yaml = <<<'yaml'
---
Yapeal:
    config: 'priority second'
...
yaml;
        $configFile2 = $this->workingDirectory . 'config2.yaml';
        $this->filesystem->dumpFile($configFile2, $yaml);
        $yaml = <<<'yaml'
---
Yapeal:
    config: 'priority first'
...
yaml;
        $configFile1 = $this->workingDirectory . 'config1.yaml';
        $this->filesystem->dumpFile($configFile1, $yaml);
        $this->beConstructedWith($dic);
        $configFiles = [
            ['pathName' => $configFile2, 'priority' => PHP_INT_MAX],
            ['pathName' => $configFile1, 'priority' => PHP_INT_MAX]
        ];
        $this->create($configFiles);
        Assert::eq($dic['Yapeal.config'], 'priority first');
    }
    public function it_removes_unprotected_settings_in_delete()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->create([$configFile]);
        Assert::count($dic->keys(), 4);
        $this->delete();
        Assert::count($dic->keys(), 0);
    }
    public function it_should_allow_empty_config_files_in_create()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->create([]);
        $this->read()
            ->shouldReturn([]);
    }
    public function it_should_return_added_config_file_structure_in_remove_config_file()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->addConfigFile($configFile);
        /**
         * @var ObjectBehavior $result
         */
        $result = $this->removeConfigFile($configFile);
        $result->shouldHaveCount(5);
        $result->shouldHaveKey('instance');
        $result->shouldHaveKeyWithValue('pathName', $configFile);
        $result->shouldHaveKey('priority');
        $result->shouldHaveKey('timestamp');
        $result->shouldHaveKey('watched');
    }
    public function it_should_return_empty_array_initially_in_read()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->read()
            ->shouldReturn([]);
    }
    public function it_should_return_false_when_path_name_is_not_watched_in_check_modified_and_update()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->addConfigFile($configFile, 1000, false);
        $this->checkModifiedAndUpdate($configFile)
            ->shouldReturn(false);
    }
    public function it_should_return_true_when_path_name_is_watched_and_has_been_updated_in_check_modified_and_update()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->addConfigFile($configFile);
        $this->filesystem->touch($configFile, time() + 15);
        $this->checkModifiedAndUpdate($configFile)
            ->shouldReturn(true);
    }
    public function it_throws_exception_when_config_files_contains_something_other_than_string_or_array_in_create()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Config file element must be a string or an array but was given integer'))
            ->during('create', [[100]]);
    }
    public function it_throws_exception_when_config_files_element_is_missing_path_name_in_create()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Config file pathName in required'))
            ->during('create', [[['junk' => 'do not care']]]);
    }
    public function it_throws_exception_when_given_duplicate_path_names_in_create()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    consoleAutoExit: true
    consoleCatchExceptions: false
    consoleName: 'Yapeal-ng Console'
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Already added config file ' . $configFile))
            ->during('create', [[$configFile, $configFile]]);
    }
    public function it_throws_exception_when_given_unknown_path_name_in_check_modified_and_update()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Tried to check unknown config file non-existing'))
            ->during('checkModifiedAndUpdate', ['non-existing']);
    }
    public function it_throws_exception_when_given_unknown_path_name_in_remove_config_file()
    {
        $dic = new Container([]);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Tried to remove unknown config file non-existing'))
            ->during('removeConfigFile', ['non-existing']);
    }
    public function it_throws_exception_when_there_is_a_circular_reference_in_create()
    {
        $yaml = <<<'yaml'
---
Yapeal:
    ref1: '{Yapeal.ref1}'
...
yaml;
        $dic = new Container([]);
        $configFile = $this->workingDirectory . 'config.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->beConstructedWith($dic, []);
        $this->shouldThrow(new \InvalidArgumentException('Exceeded maximum depth, check for possible circular reference(s)'))
            ->during('create', [[$configFile]]);
    }
    public function let()
    {
        $this->prepWorkingDirectory();
    }
    public function letGo()
    {
        $this->removeWorkingDirectory();
    }
}
