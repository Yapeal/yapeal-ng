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
class Container implements ContainerInterface, ServiceProviderContainerInterface
{
    use ContainerArrayAccessTrait;
    use ContainerMagicTrait;
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
        $this->frozen = [];
        $this->keys = [];
        $this->raw = [];
        $this->values = [];
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }
    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     *
     * @return ContainerInterface Fluent interface.
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
    private $frozen;
    /**
     * @var bool[] $keys
     */
    private $keys;
    /**
     * @var \SplObjectStorage $protected
     */
    private $protected;
    /**
     * @var mixed[] $raw
     */
    private $raw;
    /**
     * @var mixed[] $values
     */
    private $values;
}
