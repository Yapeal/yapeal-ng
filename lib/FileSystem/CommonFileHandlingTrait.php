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
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2015-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\FileSystem;

use Yapeal\Event\MediatorInterface;
use Yapeal\Log\Logger;

/**
 * Trait CommonFileHandlingTrait
 */
trait CommonFileHandlingTrait
{
    /**
     * @param                   $fileName
     * @param MediatorInterface $yem
     * @param string            $mode
     *
     * @return bool|resource
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function acquireLockedHandle($fileName, MediatorInterface $yem, $mode = 'cb+')
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
        //Give max of 10 seconds to try getting lock.
        $timeout = time() + 10;
        while (!flock($handle, LOCK_EX | LOCK_NB)) {
            if (++$tries > 20 || time() > $timeout) {
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
     * @param string            $fileName
     * @param MediatorInterface $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function deleteWithRetry($fileName, MediatorInterface $yem)
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
     * @param string            $path
     *
     * @param MediatorInterface $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    protected function isWritablePath($path, MediatorInterface $yem)
    {
        if (!is_readable($path)) {
            $mess = 'Cache path is NOT readable or does NOT exist, was given ' . $path;
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (!is_dir($path)) {
            $mess = 'Cache path is NOT a directory, was given ' . $path;
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (!is_writable($path)) {
            $mess = 'Cache path is NOT writable, was given ' . $path;
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
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
    /**
     * @param string            $data
     * @param string            $fileName
     * @param MediatorInterface $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function safeDataWrite($data, $fileName, MediatorInterface $yem)
    {
        $handle = $this->acquireLockedHandle($fileName, $yem);
        if (false === $handle) {
            return false;
        }
        $tries = 0;
        //Give 10 seconds to try writing file.
        $timeout = time() + 10;
        while (strlen($data)) {
            if (++$tries > 10 || time() > $timeout) {
                $mess = sprintf('Giving up could NOT finish writing data to %1$s', $fileName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                $this->releaseHandle($handle)
                    ->deleteWithRetry($fileName, $yem);
                return false;
            }
            $written = fwrite($handle, $data);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ($written > 0 && $tries > 0) {
                --$tries;
            }
            $data = substr($data, $written);
        }
        $this->releaseHandle($handle);
        return true;
    }
    /**
     * @param string            $fileName
     *
     * @param MediatorInterface $yem
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function safeFileRead($fileName, MediatorInterface $yem)
    {
        if (!is_readable($fileName) || !is_file($fileName)) {
            $mess = 'Could NOT find accessible file, was given ' . $fileName;
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
            return false;
        }
        $handle = $this->acquireLockedHandle($fileName, $yem, 'rb+');
        if (false === $handle) {
            return false;
        }
        rewind($handle);
        $data = '';
        $tries = 0;
        //Give 10 seconds to try reading file.
        $timeout = time() + 10;
        while (!feof($handle)) {
            if (++$tries > 10 || time() > $timeout) {
                $mess = sprintf('Giving up could NOT finish reading data from %1$s', $fileName);
                $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                $this->releaseHandle($handle);
                return false;
            }
            $read = fread($handle, 16384);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ('' !== $read && $tries > 0) {
                --$tries;
            }
            $data .= $read;
        }
        $this->releaseHandle($handle);
        return $data;
    }
    /**
     * Safely write file using lock and temp file.
     *
     * @param string            $data
     * @param string            $pathFile
     * @param MediatorInterface $yem
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function safeFileWrite($data, $pathFile, MediatorInterface $yem)
    {
        $path = dirname($pathFile);
        $suffix = substr(strrchr($pathFile, '.'), 1);
        $baseFile = basename($pathFile, $suffix);
        if (false === $this->isWritablePath($path, $yem)) {
            return false;
        }
        if (false === $this->deleteWithRetry($pathFile, $yem)) {
            return false;
        }
        $tmpFile = sprintf('%1$s/%2$s.tmp', $path, $baseFile);
        if (false === $this->safeDataWrite($data, $tmpFile, $yem)) {
            return false;
        }
        if (false === rename($tmpFile, $pathFile)) {
            $mess = sprintf('Could NOT rename %1$s to %2$s', $tmpFile, $pathFile);
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        return true;
    }
}
