<?php
/**
 * Contains CommandToolsTrait trait.
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
namespace Yapeal;

use Yapeal\Container\ContainerInterface;
use Yapeal\Exception\YapealDatabaseException;
use Yapeal\Sql\CommonSqlQueries;

/**
 * Trait CommandToolsTrait
 */
trait CommonToolsTrait
{
    /**
     * @return ContainerInterface
     * @throws \LogicException
     */
    public function getDic()
    {
        if (!$this->dic instanceof ContainerInterface) {
            $mess = 'Tried to use dic before it was set';
            throw new \LogicException($mess, 1);
        }
        return $this->dic;
    }
    /**
     * @param CommonSqlQueries $value
     *
     * @return self Fluent interface.
     */
    public function setCsq(CommonSqlQueries $value)
    {
        $this->csq = $value;
        return $this;
    }
    /**
     * @param ContainerInterface $value
     *
     * @return self Fluent interface.
     */
    public function setDic(ContainerInterface $value)
    {
        $this->dic = $value;
        return $this;
    }
    /**
     * @param \PDO $value
     *
     * @return self Fluent interface.
     */
    public function setPdo(\PDO $value)
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @return CommonSqlQueries
     * @throws \LogicException
     */
    protected function getCsq()
    {
        if (null === $this->csq) {
            $this->csq = $this->getDic()['Yapeal.Sql.CommonQueries'];
        }
        if (!$this->csq instanceof CommonSqlQueries) {
            $mess = 'Tried to use csq before it was set';
            throw new \LogicException($mess, 1);
        }
        return $this->csq;
    }
    /**
     * @return \PDO
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealDatabaseException
     */
    protected function getPdo()
    {
        if (null === $this->pdo) {
            try {
                $this->pdo = $this->getDic()['Yapeal.Sql.Connection'];
            } catch (\PDOException $exc) {
                $mess = sprintf('Could NOT connect to database. Database error was (%1$s) %2$s',
                    $exc->getCode(),
                    $exc->getMessage());
                throw new YapealDatabaseException($mess, 1, $exc);
            }
        }
        return $this->pdo;
    }
    /**
     * @var CommonSqlQueries $csq
     */
    protected $csq;
    /**
     * @var ContainerInterface $dic
     */
    protected $dic;
    /**
     * @var \PDO $pdo
     */
    protected $pdo;
}
