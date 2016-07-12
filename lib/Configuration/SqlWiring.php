<?php
/**
 * Contains class SqlWiring.
 *
 * PHP version 5.5
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealDatabaseException;

/**
 * Class SqlWiring.
 */
class SqlWiring implements WiringInterface
{
    /**
     * @param ContainerInterface $dic
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    public function wire(ContainerInterface $dic)
    {
        if (empty($dic['Yapeal.Sql.CommonQueries'])) {
            $dic['Yapeal.Sql.CommonQueries'] = function ($dic) {
                return new $dic['Yapeal.Sql.Handlers.queries']($dic['Yapeal.Sql.database'],
                    $dic['Yapeal.Sql.tablePrefix']);
            };
        }
        if (empty($dic['Yapeal.Sql.Connection'])) {
            if ('mysql' !== $dic['Yapeal.Sql.platform']) {
                $mess = 'Unknown platform, was given ' . $dic['Yapeal.Sql.platform'];
                throw new YapealDatabaseException($mess);
            }
            $dic['Yapeal.Sql.Connection'] = function ($dic) {
                $dsn = '%1$s:host=%2$s;charset=utf8mb4';
                $subs = [$dic['Yapeal.Sql.platform'], $dic['Yapeal.Sql.hostName']];
                if (!empty($dic['Yapeal.Sql.port'])) {
                    $dsn .= ';port=%3$s';
                    $subs[] = $dic['Yapeal.Sql.port'];
                }
                /**
                 * @var \PDO $database
                 */
                $database = new $dic['Yapeal.Sql.Handlers.connection'](vsprintf($dsn, $subs),
                    $dic['Yapeal.Sql.userName'],
                    $dic['Yapeal.Sql.password']);
                $database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $database->exec('SET SESSION SQL_MODE=\'ANSI,TRADITIONAL\'');
                $database->exec('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $database->exec('SET SESSION TIME_ZONE=\'+00:00\'');
                $database->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_520_ci');
                $database->exec('SET COLLATION_CONNECTION=utf8mb4_unicode_520_ci');
                $database->exec('SET DEFAULT_STORAGE_ENGINE=' . $dic['Yapeal.Sql.engine']);
                return $database;
            };
        }
        if (empty($dic['Yapeal.Sql.Creator'])) {
            $dic['Yapeal.Sql.Creator'] = function () use ($dic) {
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
                $create = new $dic['Yapeal.Sql.Handlers.create']($twig,
                    $dic['Yapeal.Sql.dir'],
                    $dic['Yapeal.Sql.platform']);
                if (!empty($dic['Yapeal.Create.overwrite'])) {
                    $create->setOverwrite($dic['Yapeal.Create.overwrite']);
                }
                return $create;
            };
        }
        if (empty($dic['Yapeal.Event.Mediator'])) {
            $mess = 'Tried to call Mediator before it has been added';
            throw new \LogicException($mess);
        }
        /**
         * @var \Yapeal\Event\MediatorInterface $mediator
         */
        $mediator = $dic['Yapeal.Event.Mediator'];
        $mediator->addServiceSubscriberByEventList('Yapeal.Sql.Creator',
            ['Yapeal.EveApi.create' => ['createSql', 'last']]);
        return $this;
    }
}
