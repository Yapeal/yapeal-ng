<?php
declare(strict_types = 1);
/**
 * Contains class ActivationStrategy.
 *
 * PHP version 7.0+
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
 * <http://spdx.org/licenses/LGPL-3.0.html>.
 *
 * You should be able to find a copy of this license in the COPYING-LESSER.md
 * file. A copy of the GNU GPL should also be available in the COPYING.md file.
 *
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;

/**
 * Class ActivationStrategy.
 *
 * A runtime instead of just new instance settable error level activation strategy like what is include with Monolog.
 */
class ActivationStrategy implements ActivationStrategyInterface
{
    /**
     * ActivationStrategy constructor.
     *
     * @param int $actionLevel
     */
    public function __construct(int $actionLevel)
    {
        $this->actionLevel = $this->setActionLevel($actionLevel);
    }
    /**
     * @param array $record
     *
     * @return bool
     */
    public function isHandlerActivated(array $record): bool
    {
        return $record['level'] >= $this->actionLevel;
    }
    /**
     * @param int $value
     */
    public function setActionLevel(int $value)
    {
        $this->actionLevel = Logger::toMonologLevel($value);
    }
    /**
     * @var int $actionLevel
     */
    private $actionLevel;
}
