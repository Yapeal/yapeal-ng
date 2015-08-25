<?php
/**
 * Contains CommonFileHandlingTrait Trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Trait CommonFileHandlingTrait
 */
trait CommonFileHandlingTrait
{
    /**
     * @param                        $fileName
     * @param EventMediatorInterface $yem
     * @param string                 $mode
     *
     * @return bool|resource
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function acquireLockedHandle($fileName, EventMediatorInterface $yem, $mode = 'cb+')
    {
        $handle = fopen($fileName, $mode, false);
        if (false === $handle) {
            $mess = sprintf('Failed to get %1$s file handle', $fileName);
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (false === $this->acquiredLock($handle)) {
            $mess = sprintf('Failed to get exclusive lock to %1$s', $fileName);
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        return $handle;
    }
    /**
     * @param resource $handle
     *
     * @return bool
     */
    protected function acquiredLock($handle)
    {
        $tries = 0;
        //Give 10 seconds to try getting lock.
        $timeout = time() + 10;
        while (!flock($handle, LOCK_EX | LOCK_NB)) {
            if (++$tries > 10 || time() > $timeout) {
                return false;
            }
            // Wait 0.01 to 0.5 seconds before trying again.
            usleep(mt_rand(10000, 500000));
        }
        return true;
    }
    /**
     * Used to delete a file when unlink might fail and it needs to be retried.
     *
     * @param string                 $fileName
     * @param EventMediatorInterface $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function deleteWithRetry($fileName, EventMediatorInterface $yem)
    {
        clearstatcache(true, $fileName);
        if (!is_file($fileName)) {
            return true;
        }
        // Acquire exclusive access to file to help prevent conflicts when deleting.
        $handle = $this->acquireLockedHandle($fileName, $yem, 'rb+');
        $tries = 0;
        do {
            if (is_resource($handle)) {
                ftruncate($handle, 0);
                rewind($handle);
                flock($handle, LOCK_UN);
                fclose($handle);
            }
            if (++$tries > 10) {
                $mess = sprintf('To many retries when trying to delete %1$s', $fileName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                return false;
            }
            // Wait 0.01 to 0.5 seconds before trying again.
            usleep(mt_rand(10000, 500000));
        } while (false === unlink($fileName));
        clearstatcache(true, $fileName);
        return true;
    }
    /**
     * @param resource $handle
     *
     * @return self Fluent interface.
     */
    protected function releaseHandle($handle)
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        return $this;
    }
}
