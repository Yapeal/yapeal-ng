<?php
declare(strict_types = 1);
/**
 * Contains class EveApiWiring.
 *
 * PHP version 7.0
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
namespace Yapeal\Configuration;

use FilePathNormalizer\FilePathNormalizerTrait;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\MediatorInterface;

/**
 * Class EveApiWiring.
 */
class EveApiWiring implements WiringInterface
{
    use FilePathNormalizerTrait;
    /**
     * @param ContainerInterface $dic
     *
     * @throws \LogicException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Event.Mediator'])) {
            $mess = 'Tried to call Mediator before it has been added';
            throw new \LogicException($mess);
        }
        /**
         * @var MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $internal = $this->getFilteredEveApiSubscriberList($dic);
        if (0 !== count($internal)) {
            foreach ($internal as $listener) {
                $service = sprintf('%1$s.%2$s.%3$s',
                    'Yapeal.EveApi',
                    basename(dirname($listener)),
                    basename($listener, '.php'));
                if (empty($dic[$service])) {
                    $dic[$service] = function () use ($dic, $service) {
                        $class = '\\' . str_replace('.', '\\', $service);
                        /**
                         * @var \Yapeal\CommonToolsInterface $callable
                         */
                        $callable = new $class();
                        $callable->setCsq($dic['Yapeal.Sql.CommonQueries'])
                            ->setPdo($dic['Yapeal.Sql.Connection']);
                        if (false === strpos($service, 'Section')) {
                            /**
                             * @var \Yapeal\Event\EveApiPreserverInterface $callable
                             */
                            $callable->setPreserve((bool)$dic['Yapeal.EveApi.Cache.preserve']);
                        }
                        return $callable;
                    };
                }
                $mediator->addServiceListener($service . '.start', [$service, 'startEveApi'], 'last');
                if (false === strpos($listener, 'Section')) {
                    $mediator->addServiceListener($service . '.preserve', [$service, 'preserveEveApi'], 'last');
                }
            }
        }
        $this->wireCreator($dic, $mediator);
    }
    /**
     * @param ContainerInterface $dic
     *
     * @return array
     */
    private function getFilteredEveApiSubscriberList(ContainerInterface $dic): array
    {
        clearstatcache(true);
        $globPath = $dic['Yapeal.EveApi.dir'] . '{Account,Api,Char,Corp,Eve,Map,Server}/*.php';
        $fileNames = glob($globPath, GLOB_NOESCAPE | GLOB_BRACE);
        if (false === $fileNames) {
            $fileNames = [];
        }
        return $fileNames;
    }
    /**
     * @param ContainerInterface $dic
     * @param MediatorInterface  $mediator
     */
    private function wireCreator(ContainerInterface $dic, MediatorInterface $mediator)
    {
        if (empty($dic['Yapeal.EveApi.Creator'])) {
            $dic['Yapeal.EveApi.Creator'] = function () use ($dic) {
                $loader = new \Twig_Loader_Filesystem($dic['Yapeal.EveApi.dir']);
                $twig = new \Twig_Environment($loader,
                    ['debug' => true, 'strict_variables' => true, 'autoescape' => false]);
                $filter = new \Twig_SimpleFilter('ucFirst', function ($value) {
                    return ucfirst($value);
                });
                $twig->addFilter($filter);
                $filter = new \Twig_SimpleFilter('lcFirst', function ($value) {
                    return lcfirst($value);
                });
                $twig->addFilter($filter);
                /**
                 * @var \Yapeal\EveApi\Creator $create
                 */
                $create = new $dic['Yapeal.EveApi.Handlers.create']($twig, $dic['Yapeal.EveApi.dir']);
                if (!empty($dic['Yapeal.Create.overwrite'])) {
                    $create->setOverwrite($dic['Yapeal.Create.overwrite']);
                }
                return $create;
            };
            $mediator->addServiceListener('Yapeal.EveApi.create', ['Yapeal.EveApi.Creator', 'createEveApi'], 'last');
        }
    }
}
