<?php
declare(strict_types = 1);
/**
 * Contains SafeFileHandlingTrait Trait.
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

use FilePathNormalizer\FilePathNormalizerTrait;

/**
 * Trait SafeFileHandlingTrait
 */
trait SafeFileHandlingTrait
{
    use FilePathNormalizerTrait;
    /**
     * Safely read contents of file.
     *
     * @param string $pathFile File name with absolute path.
     *
     * @return false|string Returns the file contents or false for any problems that prevent it.
     */
    protected function safeFileRead(string $pathFile)
    {
        try {
            $pathFile = $this->getFpn()
                ->normalizeFile($pathFile);
        } catch (\Exception $exc) {
            return false;
        }
        // Insure file info is fresh.
        clearstatcache(true, $pathFile);
        if (!is_readable($pathFile) || !is_file($pathFile)) {
            return false;
        }
        return $this->safeDataRead($pathFile);
    }
    /**
     * Safely write file using lock and temp file.
     *
     * @param string $pathFile File name with absolute path.
     *
     * @param string $data     Contents to be written to file.
     *
     * @return bool Returns true if contents written, false on any problem that prevents write.
     */
    protected function safeFileWrite(string $pathFile, string $data): bool
    {
        try {
            $pathFile = $this->getFpn()
                ->normalizeFile($pathFile);
        } catch (\Exception $exc) {
            return false;
        }
        $path = dirname($pathFile);
        $baseFile = basename($pathFile);
        if (false === $this->isWritablePath($path)) {
            return false;
        }
        $tmpFile = sprintf('%1$s/%2$s.tmp', $path, hash('sha1', $baseFile . random_bytes(8)));
        if (false === $this->safeDataWrite($tmpFile, $data)) {
            return false;
        }
        if (false === $this->deleteWithRetry($pathFile)) {
            return false;
        }
        if (false === $renamed = rename($tmpFile, $pathFile)) {
            $this->deleteWithRetry($tmpFile);
        }
        return $renamed;
    }
    /**
     * Used to acquire a exclusively locked file handle with a given mode and time limit.
     *
     * @param string $pathFile Name of file locked file handle is for.
     * @param string $mode     Mode to open handle with. Default will create
     *                         the file if it does not exist. 'b' option should
     *                         always be used to insure cross OS compatibility.
     * @param int    $timeout  Time it seconds used while trying to get lock.
     *                         Will be internally limited between 2 and 16
     *                         seconds.
     *
     * @return false|resource Returns exclusively locked file handle resource or false on errors.
     */
    private function acquireLockedHandle(string $pathFile, string $mode = 'cb+', int $timeout = 2)
    {
        $handle = fopen($pathFile, $mode, false);
        if (false === $handle) {
            return false;
        }
        if (false === $this->acquiredLock($handle, $timeout)) {
            $this->releaseHandle($handle);
            return false;
        }
        return $handle;
    }
    /**
     * Used to acquire file handle lock that limits the time and number of tries to do so.
     *
     * @param resource $handle  File handle to acquire exclusive lock for.
     * @param int      $timeout Maximum time in seconds to wait for lock.
     *                          Internally limited between 2 and 16 seconds.
     *                          Also determines how many tries to make.
     *
     * @return bool
     */
    private function acquiredLock($handle, int $timeout): bool
    {
        $timeout = min(16, max(2, $timeout));
        // Give max of $timeout seconds or 50 * $timeout tries to getting lock.
        $maxTries = 50 * $timeout;
        $timeout += time();
        $tries = 0;
        while (!flock($handle, LOCK_EX | LOCK_NB)) {
            // Between 1/10th and 1/100th of a second randomized wait between tries used to help prevent deadlocks.
            $wait = random_int(10000, 100000);
            if (++$tries > $maxTries || (time() + $wait) >= $timeout) {
                return false;
            }
            usleep($wait);
        }
        return true;
    }
    /**
     * Used to delete a file when unlink might fail and it needs to be retried.
     *
     * @param string $pathFile File name with absolute path.
     *
     * @return bool
     */
    private function deleteWithRetry(string $pathFile): bool
    {
        clearstatcache(true, $pathFile);
        if (!is_file($pathFile)) {
            return true;
        }
        // Acquire exclusive access to file to help prevent conflicts when deleting.
        $handle = $this->acquireLockedHandle($pathFile, 'rb+');
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
        } while (false === unlink($pathFile));
        clearstatcache(true, $pathFile);
        return true;
    }
    /**
     * Checks that path is readable, writable, and a directory.
     *
     * @param string $path Absolute path to be checked.
     *
     * @return bool Return true for writable directory else false.
     */
    private function isWritablePath(string $path): bool
    {
        clearstatcache(true, $path);
        return is_readable($path) && is_dir($path) && is_writable($path);
    }
    /**
     * @param resource $handle
     *
     * @return void
     */
    private function releaseHandle($handle)
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
    /**
     * Reads data from the named file while insuring it either receives full contents or fails.
     *
     * Things that can cause read to fail:
     *
     *   * Unable to acquire exclusive file handle within calculated time or tries limits.
     *   * Read stalls without making any progress or repeatedly stalls to often.
     *   * Exceeds estimated read time based on file size with 2 second minimum enforced.
     *
     * @param string $pathFile Name of file to try reading from.
     *
     * @return false|string Returns contents of file or false for any errors that prevent it.
     */
    private function safeDataRead(string $pathFile)
    {
        $fileSize = filesize($pathFile);
        // Buffer size between 4KB and 256KB with 16MB file uses a 100KB buffer.
        $bufferSize = (1 + (int)floor(log(max(1, $fileSize), 2))) << 12;
        // Read timeout calculated by file size and write speed of
        // 16MB/sec with 2 second minimum time enforced.
        $timeout = max(2, intdiv($fileSize, 1 << 24));
        $handle = $this->acquireLockedHandle($pathFile, 'rb+', $timeout);
        if (false === $handle) {
            return false;
        }
        rewind($handle);
        $data = '';
        $tries = 0;
        $timeout += time();
        while (!feof($handle)) {
            $read = fread($handle, $bufferSize);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ('' !== $read && $tries > 0) {
                --$tries;
            }
            $data .= $read;
            if (++$tries > 10 || time() > $timeout) {
                $this->releaseHandle($handle);
                return false;
            }
        }
        $this->releaseHandle($handle);
        return $data;
    }
    /**
     * Write the data to file name using randomized tmp file, exclusive locking, and time limits.
     *
     * Things that can cause write to fail:
     *
     *   * Unable to acquire exclusive file handle within calculated time or tries limits.
     *   * Write stalls without making any progress or repeatedly stalls to often.
     *   * Exceeds estimated write time based on file size with 2 second minimum enforced.
     *
     * @param string $pathFile File name with absolute path.
     *
     * @param string $data     Contents to be written to file.
     *
     * @return bool Returns true if contents written, false on any problem that prevents write.
     */
    private function safeDataWrite(string $pathFile, string $data): bool
    {
        $amountToWrite = strlen($data);
        // Buffer size between 4KB and 256KB with 16MB file size uses a 100KB buffer.
        $bufferSize = (int)(1 + floor(log($amountToWrite, 2))) << 12;
        // Write timeout calculated by using file size and write speed of
        // 16MB/sec with 2 second minimum time enforced.
        $timeout = max(2, intdiv($amountToWrite, 1 << 24));
        $handle = $this->acquireLockedHandle($pathFile, 'cb+', $timeout);
        if (false === $handle) {
            return false;
        }
        $dataWritten = 0;
        $tries = 0;
        $timeout += time();
        do {
            $written = fwrite($handle, substr($data, $dataWritten, $bufferSize));
            // Decrease $tries while making progress but NEVER $tries <= 0.
            if ($written > 0 && $tries > 0) {
                --$tries;
            }
            $dataWritten += $written;
            if (++$tries > 10 || time() > $timeout) {
                $this->releaseHandle($handle);
                $this->deleteWithRetry($pathFile);
                return false;
            }
        } while ($dataWritten < $amountToWrite);
        $this->releaseHandle($handle);
        return true;
    }
}
