<?php
/**
 * Contains Wiring class.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use FilePathNormalizer\FilePathNormalizerTrait;
use FilesystemIterator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Traversable;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealDatabaseException;
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
     * @return self Fluent interface.
     * @throws \LogicException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     * @throws YapealDatabaseException
     */
    public function wireAll()
    {
        $names = ['Config', 'Error', 'Event', 'Log', 'Sql', 'Xml', 'Xsd', 'Xsl', 'Cache', 'Network', 'EveApi'];
        foreach ($names as $name) {
            $className = __NAMESPACE__ . '\\' . $name . 'Wiring';
            if (class_exists($className, true)) {
                /**
                 * @var WiringInterface $class
                 */
                $class = new $className();
                $class->wire($this->dic);
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
     * @throws \InvalidArgumentException
     */
    protected function doSubs(array $settings)
    {
        if (0 === count($settings)) {
            return [];
        }
        $depth = 0;
        $maxDepth = 10;
        $regEx = '/(?<all>\{(?<name>Yapeal(?:\.\w+)+)\})/';
        $dic = $this->dic;
        do {
            $settings = preg_replace_callback(
                $regEx,
                function ($match) use ($settings, $dic) {
                    if (!empty($settings[$match['name']])) {
                        return $settings[$match['name']];
                    }
                    if (!empty($dic[$match['name']])) {
                        return $dic[$match['name']];
                    }
                    return $match['all'];
                },
                $settings,
                -1,
                $count
            );
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
     * @return array
     */
    protected function getFilteredEveApiSubscriberList()
    {
        $flags = FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::UNIX_PATHS;
        $rdi = new \RecursiveDirectoryIterator($this->dic['Yapeal.EveApi.dir']);
        $rdi->setFlags($flags);
        /** @noinspection SpellCheckingInspection */
        $rcfi = new \RecursiveCallbackFilterIterator(
            $rdi, function (\SplFileInfo $current, $key, \RecursiveDirectoryIterator $rdi) {
            if ($rdi->hasChildren()) {
                return true;
            }
            $dirs = ['Account', 'Api', 'Char', 'Corp', 'Eve', 'Map', 'Server'];
            $dirExists = in_array(basename(dirname($key)), $dirs, true);
            return ($dirExists && $current->isFile() && 'php' === $current->getExtension());
        }
        );
        /** @noinspection SpellCheckingInspection */
        $rii = new RecursiveIteratorIterator(
            $rcfi, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        $rii->setMaxDepth(3);
        $fpn = $this->getFpn();
        $files = [];
        foreach ($rii as $file) {
            $files[] = $fpn->normalizeFile($file->getPathname());
        }
        return $files;
    }
    /**
     * @param string $configFile
     * @param array  $settings
     *
     * @return array|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
     */
    protected function parserConfigFile($configFile, $settings)
    {
        if (!is_readable($configFile) || !is_file($configFile)) {
            return $settings;
        }
        try {
            /**
             * @var RecursiveIteratorIterator|Traversable $rItIt
             */
            $rItIt = new RecursiveIteratorIterator(
                new RecursiveArrayIterator(
                    (new Parser())->parse(
                        file_get_contents($configFile),
                        true,
                        false
                    )
                )
            );
        } catch (ParseException $exc) {
            $mess = sprintf(
                'Unable to parse the YAML configuration file %2$s.' . ' The error message was %1$s',
                $exc->getMessage(),
                $configFile
            );
            throw new YapealException($mess, 0, $exc);
        }
        foreach ($rItIt as $leafValue) {
            $keys = [];
            /** @noinspection DisconnectedForeachInstructionInspection */
            /**
             * @var array $depths
             */
            $depths = range(0, $rItIt->getDepth());
            foreach ($depths as $depth) {
                $keys[] = $rItIt->getSubIterator($depth)
                                ->key();
            }
            $settings[implode('.', $keys)] = $leafValue;
        }
        return $this->doSubs($settings);
    }
    /**
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws YapealException
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
        if (empty($dic['Yapeal.vendorParentDir'])) {
            $vendorPos = strpos(
                $path,
                'vendor/'
            );
            if (false !== $vendorPos) {
                $dic['Yapeal.vendorParentDir'] = substr($path, 0, $vendorPos);
                $configFiles[] = $fpn->normalizeFile($dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml');
            }
        } else {
            $configFiles[] = $fpn->normalizeFile($dic['Yapeal.vendorParentDir'] . 'config/yapeal.yaml');
        }
        $settings = [];
        // Process each file in turn so any substitutions are done in a more
        // consistent way.
        foreach ($configFiles as $configFile) {
            $settings = $this->parserConfigFile(
                $configFile,
                $settings
            );
        }
        if (0 !== count($settings)) {
            // Assure NOT overwriting already existing settings.
            foreach ($settings as $key => $value) {
                $dic[$key] = empty($dic[$key]) ? $value : $dic[$key];
            }
        }
        return $this;
    }
    /**
     * @return self Fluent interface.
     */
    protected function wireEveApi()
    {
        /**
         * @var ContainerInterface $dic
         */
        $dic = $this->dic;
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $internal = $this->getFilteredEveApiSubscriberList();
        if (0 !== count($internal)) {
            $base = 'Yapeal.EveApi';
            /**
             * @var \SplFileInfo $subscriber
             */
            foreach ($internal as $subscriber) {
                $service = sprintf(
                    '%1$s.%2$s.%3$s',
                    $base,
                    basename(dirname($subscriber)),
                    basename($subscriber, '.php')
                );
                if (!isset($dic[$service])) {
                    $dic[$service] = function () use ($dic, $service) {
                        $class = '\\' . str_replace('.', '\\', $service);
                        /**
                         * @var \Yapeal\EveApi\EveApiToolsTrait $callable
                         */
                        $callable = new $class();
                        return $callable->setCsq($dic['Yapeal.Sql.CommonQueries'])
                                        ->setPdo($dic['Yapeal.Sql.Connection']);
                    };
                }
                $events = [$service . '.start' => ['startEveApi', 'last']];
                if (false === strpos($subscriber, 'Section')) {
                    $events[$service . '.preserve'] = ['preserveEveApi', 'last'];
                }
                $mediator->addServiceSubscriberByEventList($service, $events);
            }
        }
        if (empty($dic['Yapeal.EveApi.Creator'])) {
            $dic['Yapeal.EveApi.Creator'] = function () use ($dic) {
                $loader = new Twig_Loader_Filesystem($dic['Yapeal.EveApi.dir']);
                $twig = new Twig_Environment(
                    $loader, ['debug' => true, 'strict_variables' => true, 'autoescape' => false]
                );
                $filter = new Twig_SimpleFilter(
                    'ucFirst', function ($value) {
                    return ucfirst($value);
                }
                );
                $twig->addFilter($filter);
                $filter = new Twig_SimpleFilter(
                    'lcFirst', function ($value) {
                    return lcfirst($value);
                }
                );
                $twig->addFilter($filter);
                /**
                 * @var \Yapeal\EveApi\Creator $create
                 */
                $create = new $dic['Yapeal.EveApi.create']($twig, $dic['Yapeal.EveApi.dir']);
                if (!empty($dic['Yapeal.Create.overwrite'])) {
                    $create->setOverwrite($dic['Yapeal.Create.overwrite']);
                }
                return $create;
            };
            $mediator->addServiceSubscriberByEventList(
                'Yapeal.EveApi.Creator',
                ['Yapeal.EveApi.create' => ['createEveApi', 'last']]
            );
        }
        return $this;
    }
    /**
     * @var ContainerInterface $dic
     */
    protected $dic;
}
