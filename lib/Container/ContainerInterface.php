<?php
declare(strict_types = 1);
/**
 * Contains ContainerInterface Interface.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2017 Michael Cummings
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
 * @copyright 2014-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Container;

/**
 * Interface ContainerInterface
 *
 * Abstracts out the interface used in Pimple 2.0.0 which is a little different
 * than what was used in Pimple 1.0.x. Most noticeable change is by default it
 * makes only one instance of the object vs in the old where it acted more like
 * a factory giving you new instances each time. The old functionally is
 * available by using ContainerInterface::factory() if still needed.
 *
 * @since 2.0.0-alpha5
 */
interface ContainerInterface extends \ArrayAccess
{
    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string|int $id       The unique identifier for the object
     * @param callable   $callable A service definition to extend the original
     *
     * @return callable The wrapped callable
     *
     * @throws \InvalidArgumentException If the identifier is NOT defined or NOT
     * a service definition
     */
    public function extend($id, callable $callable): callable;
    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable        A service definition to be used as a
     *                                  factory
     *
     * @return callable The passed callable
     */
    public function factory(callable $callable): callable;
    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys(): array;
    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param callable $callable A callable to protect from being evaluated
     *
     * @return callable The passed callable
     *
     * @throws \InvalidArgumentException Service definition has to be a closure
     *                                   of an invokable object
     */
    public function protect(callable $callable): callable;
    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $key The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an
     *               object
     *
     * @throws \InvalidArgumentException if the identifier is NOT defined
     */
    public function raw(string $key);
}
