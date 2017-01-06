<?php
declare(strict_types = 1);
/**
 * Contains RelativeFileSearchTrait Trait.
 *
 * PHP version 7.0+
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015-2017 Michael Cummings
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
 * @copyright 2015-2017 Michael Cummings
 * @license   LGPL-3.0+
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use Yapeal\Exception\YapealFileSystemException;

/**
 * Trait RelativeFileSearchTrait
 */
trait RelativeFileSearchTrait
{
    /**
     * Fluent interface setter for $relativeBaseDir.
     *
     * @param string $value MUST have a trailing directory separator and SHOULD be an absolute path.
     *
     * @return RelativeFileSearchTrait Fluent interface.
     */
    public function setRelativeBaseDir(string $value): self
    {
        $this->relativeBaseDir = str_replace('\\', '/', $value);
        return $this;
    }
    /**
     * Used to find a file relative to the base path using prefix, name, and suffix parts in varies ways.
     *
     * @param string $prefix Used as subdirectory or as part of file name.
     * @param string $name   Used as part of file names only.
     * @param string $suffix Used as last part of file name or by self as file name. Think file extension without
     *                       leading dot.
     *
     * @return string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealFileSystemException
     */
    protected function findRelativeFileWithPath(string $prefix, string $name, string $suffix): string
    {
        // $relDir$prefix/$name.$suffix, $relDir$prefix/$name_$suffix,
        // $relDir$prefix.$name.$suffix, $relDir$prefix_$name.$suffix, $relDir$prefix_$name_$suffix,
        // $relDir$name.$suffix, $relDir$name_$suffix,
        // $relDir$prefix.$suffix, $relDir$prefix_$suffix,
        // $relDir'common'.$suffix, $relDir'common'_$suffix, $relDir$suffix
        $combinations = '%1$s%2$s/%3$s.%4$s,%1$s%2$s/%3$s_%4$s'
            . ',%1$s%2$s.%3$s.%4$s,%1$s%2$s_%3$s.%4$s,%1$s%2$s_%3$s_%4$s'
            . ',%1$s%3$s.%4$s,%1$s%3$s_%4$s'
            . ',%1$s%2$s.%4$s,%1$s%2$s_%4$s'
            . ',%1$scommon.%4$s,%1$scommon_%4$s,%1$s%4$s';
        $fileNames = explode(',', sprintf($combinations, $this->getRelativeBaseDir(), $prefix, $name, $suffix));
        foreach ($fileNames as $fileName) {
            if (is_readable($fileName) && is_file($fileName)) {
                return $fileName;
            }
        }
        $mess = sprintf('Failed to find accessible file in %1$s using "%1$s", "%2$s", and "%3$s"',
            $this->getRelativeBaseDir(),
            $prefix,
            $name,
            $suffix);
        throw new YapealFileSystemException($mess);
    }
    /**
     * Getter for $relativeBaseDir.
     *
     * @return string
     * @throws \LogicException
     */
    private function getRelativeBaseDir(): string
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
    private $relativeBaseDir;
}
