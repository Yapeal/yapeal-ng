<?php
declare(strict_types=1);
/**
 * Contains RelativeFileSearchTrait Trait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2016 Michael Cummings
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
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use FilePathNormalizer\FilePathNormalizerTrait;
use Yapeal\Log\Logger;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/**
 * Trait RelativeFileSearchTrait
 *
 * @method \Yapeal\Event\MediatorInterface getYem()
 */
trait RelativeFileSearchTrait
{
    use FilePathNormalizerTrait;
    /**
     * Fluent interface setter for $relativeBaseDir.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setRelativeBaseDir($value)
    {
        $this->relativeBaseDir = $this->getFpn()
            ->normalizePath((string)$value);
        return $this;
    }
    /**
     * Used to find a file relative to the base path using section and api names for path and/or file name.
     *
     * @param string $sectionName
     * @param string $apiName
     * @param string $suffix
     *
     * @return string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function findEveApiFile($sectionName, $apiName, $suffix)
    {
        $fileNames = sprintf(
            '%3$s%1$s/%2$s.%4$s,%3$s%2$s.%4$s,%3$s%1$s/%1$s.%4$s,%3$scommon.%4$s',
            $sectionName,
            $apiName,
            $this->getRelativeBaseDir(),
            $suffix
        );
        foreach ((array)explode(',', $fileNames) as $fileName) {
            $fileName = $this->getFpn()
                ->normalizeFile($fileName);
            if (is_readable($fileName) && is_file($fileName)) {
                $mess = sprintf(
                    'Using %4$s file %3$s for %1$s/%2$s',
                    ucfirst($sectionName),
                    $apiName,
                    $fileName,
                    strtoupper($suffix)
                );
                $this->getYem()
                    ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
                return $fileName;
            }
        }
        $mess = sprintf(
            'Failed to find accessible %3$s file for %1$s/%2$s, check file permissions',
            $sectionName,
            $apiName,
            strtoupper($suffix)
        );
        $this->getYem()
            ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
        return '';
    }
    /**
     * Getter for $relativeBaseDir.
     *
     * @return string
     * @throws \LogicException
     */
    protected function getRelativeBaseDir()
    {
        if (null === $this->relativeBaseDir) {
            $mess = 'Tried to use relativeBaseDir before it was set';
            throw new \LogicException($mess);
        }
        return $this->relativeBaseDir;
    }
    /**
     * Holds the path that is prepended for searches.
     *
     * @var string $relativeBaseDir
     */
    protected $relativeBaseDir;
}
