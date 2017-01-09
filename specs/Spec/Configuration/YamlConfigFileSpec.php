<?php
declare(strict_types = 1);
/**
 * Contains class YamlConfigFileSpec.
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
use Yapeal\Configuration\YamlConfigFile;

//use Prophecy\Argument;
/**
 * Class YamlConfigFileSpec
 *
 * @mixin \Yapeal\Configuration\YamlConfigFile
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class YamlConfigFileSpec extends ObjectBehavior
{
    use FileSystemUtilTrait;
    /**
     * YamlConfigFileSpec constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }
    public function __destruct()
    {
        if (null !== $this->workingDirectory) {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @rmdir(dirname($this->workingDirectory));
        }
    }
    public function it_is_initializable()
    {
        $this->shouldHaveType(YamlConfigFile::class);
    }
    public function it_should_clear_settings_when_config_file_does_not_exist_in_read()
    {
        $this->setSettings(['Yapeal.junk' => 'test1']);
        $this->getSettings()
            ->shouldReturn(['Yapeal.junk' => 'test1']);
        $this->prepWorkingDirectory();
        $this->setPathFile($this->workingDirectory . 'IDoNotExist.yaml')
            ->read()
            ->getSettings()
            ->shouldReturn([]);
        $this->removeWorkingDirectory();
    }
    public function it_should_have_settings_after_reading_existing_config_file_in_read()
    {
        $this->getSettings()
            ->shouldReturn([]);
        $this->prepWorkingDirectory();
        $yaml = <<<'yaml'
---
Yapeal:
    version: '0.6.0-0-gafa3c59'
...
yaml;
        $configFile = $this->workingDirectory . 'junk.yaml';
        $this->filesystem->dumpFile($configFile, $yaml);
        $this->setPathFile($configFile)
            ->read()
            ->getSettings()
            ->shouldReturn(['Yapeal' => ['version' => '0.6.0-0-gafa3c59']]);
        $this->removeWorkingDirectory();
    }
    public function it_should_have_written__existing_settings_to_config_file_in_save()
    {
        $this->setSettings(['Yapeal' => ['version' => '0.6.0-0-gafa3c59']]);
        $this->prepWorkingDirectory();
        $yaml = <<<'yaml'
---
Yapeal:
    version: 0.6.0-0-gafa3c59
...
yaml;
        $configFile = $this->workingDirectory . 'junk.yaml';
        $this->setPathFile($configFile)
            ->save();
        Assert::file($configFile);
        $result = file_get_contents($configFile);
        Assert::eq($result, $yaml);
        $this->removeWorkingDirectory();
    }
    public function it_should_return_empty_array_if_given_empty_array_in_flatten_yaml()
    {
        $this->flattenYaml()
            ->shouldReturn([]);
        $this->flattenYaml([])
            ->shouldReturn([]);
    }
    public function it_should_return_empty_array_if_given_empty_array_in_unflatten_yaml()
    {
        $this->unflattenYaml()
            ->shouldReturn([]);
        $this->unflattenYaml([])
            ->shouldReturn([]);
    }
    public function it_should_return_multi_dimensional_array_when_unflatten_yaml()
    {
        $yaml = [
            'Yapeal.Junk.first' => 'test1',
            'Yapeal.Junk.second' => 'test2',
            'Yapeal.Junk1.Deep.first' => ['test3', 'test4'],
            'Yapeal.Junk1.Deep.second' => ['Deeper.third' => 'test5']
        ];
        $expected = [
            'Yapeal' => [
                'Junk' => ['first' => 'test1', 'second' => 'test2'],
                'Junk1' => [
                    'Deep' => [
                        'first' => ['test3', 'test4'],
                        'second' => ['Deeper.third' => 'test5']
                    ]
                ]
            ],
            'Yapeal.Junk1.Deep.first' => ['test3', 'test4'],
            'Yapeal.Junk1.Deep.second' => [
                'Deeper' => [
                    'third' => 'test5'
                ]
            ]
        ];
        $this->unflattenYaml($yaml)
            ->shouldReturn($expected);
    }
    public function it_should_return_self_from_set_path_file()
    {
        $this->setPathFile('')
            ->shouldReturn($this);
    }
    public function it_should_return_self_from_set_settings()
    {
        $this->setSettings([])
            ->shouldReturn($this);
    }
    public function it_should_return_set_path_from_get_path_file()
    {
        $this->setPathFile('junk');
        $this->getPathFile()
            ->shouldReturn('junk');
    }
    public function it_should_return_single_dimensional_array_when_flatten_yaml()
    {
        $yaml = [
            'Yapeal' => [
                'Junk' => ['first' => 'test1', 'second' => 'test2'],
                'Junk1' => [
                    'Deep' => [
                        'first' => ['test3', 'test4'],
                        'second' => ['Deeper.third' => 'test5']
                    ]
                ]
            ]
        ];
        $expected = [
            'Yapeal.Junk.first' => 'test1',
            'Yapeal.Junk.second' => 'test2',
            'Yapeal.Junk1.Deep.first.0' => 'test3',
            'Yapeal.Junk1.Deep.first.1' => 'test4',
            'Yapeal.Junk1.Deep.second.Deeper.third' => 'test5'
        ];
        $this->flattenYaml($yaml)
            ->shouldReturn($expected);
    }
    public function it_throws_exception_when_path_file_not_set_in_get_path_file()
    {
        $mess = 'Trying to access $pathFile before it was set';
        $this->shouldThrow(new \LogicException($mess))
            ->during('getPathFile');
    }
    public function it_throws_exception_when_path_file_not_set_in_read()
    {
        $mess = 'Path file must be set before trying to read config file';
        $this->shouldThrow(new \BadMethodCallException($mess, 1))
            ->duringRead();
    }
    public function it_throws_exception_when_path_file_not_set_in_write()
    {
        $mess = 'Path file must be set before trying to save config file';
        $this->shouldThrow(new \BadMethodCallException($mess, 1))
            ->duringSave();
    }
}
