<?php
declare(strict_types = 1);
/**
 * Contains class WiringSpec.
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
 * @copyright 2016-2017 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\Yapeal\Configuration;

use PhpSpec\ObjectBehavior;
use Yapeal\Container\ContainerInterface;

/**
 * Class WiringSpec
 *
 * @mixin \Yapeal\Configuration\Wiring
 *
 * @method void during($method, array $params)
 * @method void shouldBe($value)
 * @method void shouldContain($value)
 * @method void shouldHaveKey($key)
 * @method void shouldHaveType($value)
 * @method void shouldImplement($interface)
 * @method void shouldNotEqual($value)
 * @method void shouldReturn($result)
 */
class WiringSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Yapeal\Configuration\Wiring');
    }
    public function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }
}
