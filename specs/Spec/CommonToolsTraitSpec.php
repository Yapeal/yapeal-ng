<?php
declare(strict_types = 1);
/**
 * Contains class CommonToolsTraitSpec.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Spec
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
namespace Spec\Yapeal;

use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Yapeal\Container\ContainerInterface;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Sql\ConnectionInterface;

/**
 * Class CommonToolsTraitSpec
 *
 * @mixin \Yapeal\CommonToolsTrait
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class CommonToolsTraitSpec extends ObjectBehavior
{
    /**
     * @param Collaborator|\Yapeal\Container\ContainerInterface $dic
     *
     * @throws \LogicException
     */
    public function it_should_let_you_get_back_same_dic_you_give_it(ContainerInterface $dic)
    {
        $this->setDic($dic);
        $this->getDic()
            ->shouldReturn($dic);
    }
    /**
     * @param Collaborator|\Yapeal\Sql\CommonSqlQueries $csq
     */
    public function it_should_let_you_set_csq(CommonSqlQueries $csq)
    {
        $this->setCsq($csq);
    }
    /**
     * @param Collaborator|ConnectionInterface $pdo
     *
     * @throws \InvalidArgumentException
     */
    public function it_should_let_you_set_pdo(ConnectionInterface $pdo)
    {
        $pdo->isSql92Mode()->willReturn(true);
        $this->setPdo($pdo);
    }
    public function it_throws_exception_when_accessing_dic_before_it_is_set()
    {
        $mess = 'Trying to access $dic before it was set';
        $this->shouldThrow(new \LogicException($mess, 1))
            ->during('getDic');
    }
    public function let()
    {
        $this->beAnInstanceOf(MockCommonTools::class);
    }
}
