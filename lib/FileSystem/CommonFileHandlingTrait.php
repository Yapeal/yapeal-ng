<?php
declare(strict_types = 1);
/**
 * Contains CommonFileHandlingTrait Trait.
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

/**
 * Trait CommonFileHandlingTrait
 */
trait CommonFileHandlingTrait
{
    use FilePathNormalizerTrait;
    /**
     * @param string $fileName
     *
     * @return false|string
     */
    protected function safeFileRead(string $fileName)
    {
        $fileName = $this->getFpn()
            ->normalizeFile($fileName);
        if (!is_readable($fileName) || !is_file($fileName)) {
            return false;
        }
        return $this->safeDataRead($fileName);
    }
    /**
     * Safely write file using lock and temp file.
     *
     * @param string $data
     * @param string $pathFile
     *
     * @return bool
     */
    protected function safeFileWrite(string $data, string $pathFile): bool
    {
        $pathFile = $this->getFpn()
            ->normalizeFile($pathFile);
        $path = dirname($pathFile);
        $suffix = substr(strrchr($pathFile, '.'), 1);
        $baseFile = basename($pathFile, $suffix);
        if (false === $this->isWritablePath($path)) {
            return false;
        }
        if (false === $this->deleteWithRetry($pathFile)) {
            return false;
        }
        $tmpFile = sprintf('%1$s/%2$s.tmp', $path, hash('sha1', $baseFile . microtime()));
        if (false === $this->safeDataWrite($data, $tmpFile)) {
            return false;
        }
        return rename($tmpFile, $pathFile);
    }
    /**
     * @param string $fileName
     * @param string $mode
     *
     * @return bool|resource
     */
    private function acquireLockedHandle(string $fileName, string $mode = 'cb+')
    {
        $handle = fopen($fileName, $mode, false);
        if (false === $handle || false === $this->acquiredLock($handle)) {
            return false;
        }
        return $handle;
    }
    /**
     * @param resource $handle
     *
     * @return bool
     */
    private function acquiredLock($handle): bool
    {
        $tries = 0;
        //Give max of 10 seconds or 20 tries to getting lock.
        $timeout = time() + 10;
        while (!flock($handle, LOCK_EX | LOCK_NB)) {
            if (++$tries > 20 || time() > $timeout) {
                return false;
            }
            // Wait 0.01 to 0.5 seconds before trying again.
            usleep(random_int(10000, 500000));
        }
        return true;
    }
    /**
     * Used to delete a file when unlink might fail and it needs to be retried.
     *
     * @param string $fileName
     *
     * @return bool
     */
    private function deleteWithRetry(string $fileName): bool
    {
        clearstatcache(true, $fileName);
        if (!is_file($fileName)) {
            return true;
        }
        // Acquire exclusive access to file to help prevent conflicts when deleting.
        $handle = $this->acquireLockedHandle($fileName, 'rb+');
        $tries = 0;
        do {
            if (is_resource($handle)) {
                ftruncate($handle, 0);
                rewind($handle);
                flock($handle, LOCK_UN);
                fclose($handle);
            }
            if (++$tries > 10) {
                return false;
            }
            // Wait 0.01 to 0.5 seconds before trying again.
            usleep(random_int(10000, 500000));
        } while (false === unlink($fileName));
        clearstatcache(true, $fileName);
        return true;
    }
    /**
     * @param string $path
     *
     * @return bool
     */
    private function isWritablePath(string $path): bool
    {
        return is_readable($path) && is_dir($path) && is_writable($path);
    }
    /**
     * @param resource $handle
     *
     * @return self Fluent interface.
     */
    private function releaseHandle($handle)
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        return $this;
    }
    /**
     * @param string $fileName
     *
     * @return bool|string
     */
    private function safeDataRead(string $fileName)
    {
        $handle = $this->acquireLockedHandle($fileName, 'rb+');
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
     * @param string $data
     * @param string $fileName
     *
     * @return bool
     */
    private function safeDataWrite(string $data, string $fileName): bool
    {
        $handle = $this->acquireLockedHandle($fileName);
        if (false === $handle) {
            return false;
        }
        $tries = 0;
        //Give 10 seconds to try writing file.
        $timeout = time() + 10;
        while (strlen($data)) {
            if (++$tries > 10 || time() > $timeout) {
                $this->releaseHandle($handle)
                    ->deleteWithRetry($fileName);
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
}
