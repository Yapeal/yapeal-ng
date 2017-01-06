<?php
declare(strict_types = 1);
/**
 * Contains trait ContainerArrayAccessTrait.
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
 * Trait ContainerArrayAccessTrait.
 *
 * @property array             $values
 * @property \SplObjectStorage $factories
 * @property bool[]            $frozen
 * @property bool[]            $keys
 * @property \SplObjectStorage $protected
 * @property mixed[]           $raw
 */
trait ContainerArrayAccessTrait
{
    /**
     * Checks if a parameter or an object is set.
     *
     * @param string|int $id The unique identifier for the parameter or object
     *
     * @return bool
     */
    public function offsetExists($id): bool
    {
        return array_key_exists($id, $this->keys);
    }
    /**
     * Gets a parameter or an object.
     *
     * @param string|int $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id)
    {
        if (!$this->offsetExists($id)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if (array_key_exists($id, $this->raw)
            || !is_object($this->values[$id])
            || isset($this->protected[$this->values[$id]])
            || !method_exists($this->values[$id], '__invoke')
        ) {
            return $this->values[$id];
        }
        if (isset($this->factories[$this->values[$id]])) {
            return $this->values[$id]($this);
        }
        $raw = $this->values[$id];
        $val = $this->values[$id] = $raw($this);
        $this->raw[$id] = $raw;
        $this->frozen[$id] = true;
        return $val;
    }
    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same name as an existing parameter would break your container).
     *
     * @param string|int $id    The unique identifier for the parameter or object
     * @param mixed      $value The value of the parameter or a closure to define an object
     *
     * @throws \RuntimeException Prevent override of a frozen service
     */
    public function offsetSet($id, $value)
    {
        if (array_key_exists($id, $this->frozen)) {
            throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
        }
        $this->values[$id] = $value;
        $this->keys[$id] = true;
    }
    /**
     * Un-sets a parameter or an object.
     *
     * @param string|int $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if ($this->offsetExists($id)) {
            if (is_object($this->values[$id])) {
                unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
            }
            unset($this->values[$id], $this->frozen[$id], $this->raw[$id], $this->keys[$id]);
        }
    }
}
