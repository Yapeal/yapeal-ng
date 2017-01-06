<?php
declare(strict_types = 1);
/**
 * Contains trait ContainerMagicTrait.
 *
 * PHP version 7.0+
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
 * @license   LGPL-3.0+
 */
namespace Yapeal\Container;

/**
 * Trait ContainerMagicTrait.
 *
 * @method bool offsetExists($id)
 * @property array $values
 * @property \SplObjectStorage $factories
 * @property bool[] $keys
 * @property \SplObjectStorage $protected
 * @property mixed[] $raw
 */
trait ContainerMagicTrait
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
     * @throws \InvalidArgumentException if the identifier is not defined or not a service definition
     */
    public function extend($id, callable $callable): callable
    {
        if (!$this->offsetExists($id)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if (!is_object($this->values[$id]) || !method_exists($this->values[$id], '__invoke')) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }
        $factory = $this->values[$id];
        $extended = function ($c) use ($callable, $factory) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            return $callable($factory($c), $c);
        };
        if (isset($this->factories[$factory])) {
            $this->factories->detach($factory);
            $this->factories->attach($extended);
        }
        return $this[$id] = $extended;
    }
    /**
     * Marks a callable as being a factory service.
     *
     * @param callable $callable A service definition to be used as a factory
     *
     * @return callable The passed callable
     */
    public function factory(callable $callable): callable
    {
        /** @noinspection PhpParamsInspection */
        $this->factories->attach($callable);
        return $callable;
    }
    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys(): array
    {
        return array_keys($this->keys);
    }
    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param callable $callable A callable to protect from being evaluated
     *
     * @return callable The passed callable
     *
     * @throws \InvalidArgumentException Service definition has to be a closure of an invokable object
     */
    public function protect(callable $callable): callable
    {
        /** @noinspection PhpParamsInspection */
        $this->protected->attach($callable);
        return $callable;
    }
    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    public function raw(string $id)
    {
        if (!$this->offsetExists($id)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if (array_key_exists($id, $this->raw)) {
            return $this->raw[$id];
        }
        return $this->values[$id];
    }
}
