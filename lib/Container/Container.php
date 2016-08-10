<?php
declare(strict_types = 1);
/**
 * Contains class Container.
 *
 * This should act as a drop-in replacement for Pimple. In the end the wrapper
 * idea just did not work do to the design of Pimple so I re-implemented
 * everything here and use my interface.
 *
 * PHP version 7.0+
 *
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @author    Fabien Potencier
 */
namespace Yapeal\Container;

/**
 * Container class.
 *
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @since  1.1.x-WIP
 */
class Container implements ContainerInterface
{
    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     *
     * @throws \RuntimeException
     */
    public function __construct(array $values = [])
    {
        $this->factories = new \SplObjectStorage();
        $this->protected = new \SplObjectStorage();
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }
    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $id       The unique identifier for the object
     * @param callable $callable A service definition to extend the original
     *
     * @return callable The wrapped callable
     *
     * @throws \InvalidArgumentException if the identifier is not defined or not a service definition
     */
    public function extend(string $id, callable $callable): callable
    {
        if (!$this->offsetExists($id)) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        if (!is_object($this->values[$id]) || !method_exists($this->values[$id], '__invoke')) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }
        $factory = $this->values[$id];
        $extended = function ($c) use ($callable, $factory) {
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
     *
     * @throws \InvalidArgumentException Service definition has to be a closure of an invokable object
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
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return bool
     */
    public function offsetExists($id)
    {
        return (array_key_exists($id, $this->keys) && true === $this->keys[$id]);
    }
    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
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
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or a closure to define an object
     *
     * @throws \RuntimeException Prevent override of a frozen service
     */
    public function offsetSet($id, $value)
    {
        if (array_key_exists($id, $this->frozen) && true === $this->frozen[$id]) {
            throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
        }
        $this->values[$id] = $value;
        $this->keys[$id] = true;
    }
    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
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
    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return ContainerInterface
     */
    public function register(ServiceProviderInterface $provider, array $values = []): ContainerInterface
    {
        $provider->register($this);
        foreach ($values as $key => $value) {
            $this[(string)$key] = $value;
        }
        return $this;
    }
    /**
     * @var \SplObjectStorage $factories
     */
    private $factories;
    /**
     * @var bool[] $frozen
     */
    private $frozen = [];
    /**
     * @var bool[] $keys
     */
    private $keys = [];
    /**
     * @var \SplObjectStorage $protected
     */
    private $protected;
    /**
     * @var mixed[] $raw
     */
    private $raw = [];
    /**
     * @var mixed[] $values
     */
    private $values = [];
}
