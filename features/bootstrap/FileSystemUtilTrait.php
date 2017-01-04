<?php
declare(strict_types = 1);
/**
 * Contains trait FileSystemUtilTrait.
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
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2016 Michael Cummings
 * @license   LGPL-3.0+
 */
namespace Yapeal\Behat;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait FileSystemUtilTrait.
 */
trait FileSystemUtilTrait
{
    /**
     * @BeforeScenario
     */
    public function prepWorkingDirectory()
    {
        $path = str_replace('\\', '/', __DIR__);
        $this->projectBase = substr($path, 0, strpos($path, 'features/'));
        $path = str_replace('\\', '/', sys_get_temp_dir());
        $this->workingDirectory = sprintf('%s/%s/%s/',
            $path,
            basename($this->projectBase),
            hash('sha1', basename($this->projectBase) . random_bytes(8)));
        $this->removeWorkingDirectory();
        $this->filesystem->mkdir($this->workingDirectory);
    }
    /**
     * @AfterScenario
     */
    public function removeWorkingDirectory()
    {
        $tries = 0;
        $maxTries = 5;
        do {
            try {
                if (!$this->filesystem->exists($this->workingDirectory)) {
                    break;
                }
                $this->filesystem->remove($this->workingDirectory);
            } catch (IOException $exc) {
                //ignoring exception
            }
            // Help prevent deadlocks.
            usleep(random_int(100, 1000));
        } while (++$tries < $maxTries);
    }
    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;
    /**
     * @var string $projectBase
     */
    private $projectBase;
    /**
     * @var string $workingDirectory
     */
    private $workingDirectory;
}
