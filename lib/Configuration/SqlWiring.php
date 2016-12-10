<?php
declare(strict_types = 1);
/**
 * Contains class SqlWiring.
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
namespace Yapeal\Configuration;

use Yapeal\Container\ContainerInterface;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Sql\SqlSubsTrait;

/**
 * Class SqlWiring.
 */
class SqlWiring implements WiringInterface
{
    use SqlSubsTrait;
    /**
     * @param ContainerInterface $dic
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Sql.Callable.CommonQueries'])) {
            $dic['Yapeal.Sql.Callable.CommonQueries'] = function ($dic) {
                return new $dic['Yapeal.Sql.Classes.queries']($dic);
            };
        }
        $this->wireConnection($dic);
        $this->wireCreator($dic);
    }
    /**
     * @param \Yapeal\Container\ContainerInterface $dic
     *
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    private function wireConnection(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Sql.Callable.Connection'])) {
            $replacements = $this->getSqlSubs($dic);
            $dic['Yapeal.Sql.Callable.Connection'] = function () use ($dic, $replacements) {
                $dsn = $replacements['{dsn}'];
                $dsn = str_replace(array_keys($replacements), array_values($replacements), $dsn);
                /**
                 * @var \PDO             $pdo
                 * @var CommonSqlQueries $csq
                 */
                $pdo = new $dic['Yapeal.Sql.Classes.connection']($dsn,
                    $replacements['{userName}'],
                    $replacements['{password}']);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $csq = $dic['Yapeal.Sql.Callable.CommonQueries'];
                $pdo->exec($csq->getInitialization());
                return $pdo;
            };
        }
    }
    /**
     * @param \Yapeal\Container\ContainerInterface $dic
     *
     * @throws \LogicException
     */
    private function wireCreator(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Sql.Callable.Creator'])) {
            $dic['Yapeal.Sql.Callable.Creator'] = function () use ($dic) {
                $loader = new \Twig_Loader_Filesystem($dic['Yapeal.Sql.dir']);
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
                 * @var \Yapeal\Sql\Creator $create
                 */
                $create = new $dic['Yapeal.Sql.Classes.create']($twig,
                    $dic['Yapeal.Sql.dir'],
                    $dic['Yapeal.Sql.platform']);
                if (!empty($dic['Yapeal.Create.overwrite'])) {
                    $create->setOverwrite($dic['Yapeal.Create.overwrite']);
                }
                return $create;
            };
        }
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Callable.Mediator'];
        $mediator->addServiceListener('Yapeal.EveApi.create', ['Yapeal.Sql.Callable.Creator', 'createSql'], 'last');
    }
}
