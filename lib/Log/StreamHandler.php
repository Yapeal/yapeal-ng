<?php
declare(strict_types = 1);
/**
 * Contains class StreamHandler.
 *
 * PHP version 7.0
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2016-2017 Michael Cummings
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
 * @copyright 2016-2017 Michael Cummings
 * @license   LGPL-3.0
 */
namespace Yapeal\Log;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class StreamHandler extends AbstractHandler
{
    /**
     * @param resource|string $stream
     * @param int             $level          The minimum logging level at which this handler will be triggered
     * @param bool            $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int|null        $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool            $useLocking     Try to lock log file before doing any writes
     *
     * @throws \Exception                If a missing directory is not buildable
     * @throws \InvalidArgumentException If stream is not a resource or string
     */
    public function __construct(
        $stream,
        int $level = Logger::DEBUG,
        bool $bubble = true,
        int $filePermission = null,
        bool $useLocking = false
    ) {
        parent::__construct($level, $bubble);
        if (is_resource($stream)) {
            $this->stream = $stream;
            $this->url = '';
        } elseif (is_string($stream) && '' !== $stream) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a non-empty string');
        }
        $this->filePermission = $filePermission;
        $this->preserve = true;
        $this->useLocking = $useLocking;
    }
    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close()
    {
        if ('' !== $this->url && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }
    /**
     * Return the currently active stream if it is open.
     *
     * @return null|resource
     */
    public function getStream()
    {
        return $this->stream;
    }
    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     *
     * @return bool true means that this handler handled the record, and that bubbling is not permitted.
     *              false means the record was either not processed or that this handler allows bubbling.
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $record = $this->processRecord($record);
        $record['formatted'] = $this->getFormatter()
            ->format($record);
        $this->write($record);
        return false === $this->bubble;
    }
    /**
     * Checks whether the given record will be handled by this handler.
     *
     * This is mostly done for performance reasons, to avoid calling processors for nothing.
     *
     * Handlers should still check the record levels within handle(), returning false in isHandling()
     * is no guarantee that handle() will not be called, and isHandling() might not be called
     * for a given record.
     *
     * @param array $record Partial log record containing only a level key
     *
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        if ($this->preserve) {
            return parent::isHandling($record);
        }
        return false;
    }
    /**
     * Turn on or off preserving(logging) of messages with this handler.
     *
     * Allows class to stay registered for messages but be enabled or disabled during runtime.
     *
     * @param bool $value
     *
     * @return self Fluent interface.
     */
    public function setPreserve(bool $value = true): self
    {
        $this->preserve = $value;
        return $this;
    }
    /**
     * Tries to create the required directory once.
     *
     * @throws \UnexpectedValueException
     */
    private function createDir()
    {
        // Does not try to create dir again if it has already been tried.
        if ($this->dirCreated) {
            return;
        }
        $this->dirCreated = true;
        $dir = $this->getDirFromUrl($this->url);
        if (null !== $dir && !is_dir($dir)) {
            // Ignore errors here since we're already checking result. IMHO this is better than adding and removing a
            // temp error handler like the original code did and trying to decode the received error message.
            $old = error_reporting(0);
            if (false === mkdir($dir, 0777, true)) {
                error_reporting($old);
                $mess = sprintf('There is no existing directory at "%s" and its not buildable', $dir);
                throw new \UnexpectedValueException($mess);
            }
            error_reporting($old);
        }
    }
    /**
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    private function createStream()
    {
        if (is_resource($this->stream)) {
            return;
        }
        if ('' === $this->url) {
            throw new \LogicException('Tried to write to an external stream after it was release by close');
        }
        $this->createDir();
        // Ignore errors here since we're already checking result. IMHO this is better than adding and removing a temp
        // error handler like the original code did and trying to decode the received error message.
        $old = error_reporting(0);
        $this->stream = fopen($this->url, 'ab+');
        if (null !== $this->filePermission) {
            chmod($this->url, $this->filePermission);
        }
        error_reporting($old);
        if (!is_resource($this->stream)) {
            $this->stream = null;
            $mess = sprintf('The stream or file "%s" could not be opened', $this->url);
            throw new \UnexpectedValueException($mess);
        }
    }
    /**
     * @param string $url
     *
     * @return null|string
     */
    private function getDirFromUrl(string $url)
    {
        if (false === strpos($url, '://')) {
            return dirname($url);
        }
        if (0 === strpos($url, 'file://')) {
            return dirname(substr($url, 7));
        }
        return null;
    }
    /**
     * Processes a record.
     *
     * @param  array $record
     *
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }
        return $record;
    }
    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @param  array $record
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function write(array $record)
    {
        $this->createStream();
        $handle = $this->getStream();
        // Ignoring locking errors here, there's not much we can do about them and no use blocking either.
        $this->useLocking && flock($handle, LOCK_EX | LOCK_NB);
        fwrite($handle, (string)$record['formatted']);
        $this->useLocking && flock($handle, LOCK_UN);
    }
    /**
     * @var bool $dirCreated
     */
    private $dirCreated;
    /**
     * @var int|null $filePermission
     */
    private $filePermission;
    /**
     * @var bool $preserve
     */
    private $preserve;
    /**
     * @var resource $stream
     */
    private $stream;
    /**
     * @var string $url
     */
    private $url;
    /**
     * @var bool $useLocking
     */
    private $useLocking;
}
