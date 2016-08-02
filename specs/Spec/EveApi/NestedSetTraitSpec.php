<?php
declare(strict_types=1);
/**
 * Contains class NestedSetTraitSpec.
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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Spec\Yapeal\EveApi;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class NestedSetTraitSpec
 *
 * @mixin \Spec\Yapeal\EveApi\NestedSetTraitSpec
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
class NestedSetTraitSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf('\Yapeal\EveApi\NestedSetTrait');
    }
}
