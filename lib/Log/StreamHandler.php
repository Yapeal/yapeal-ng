<?php
declare(strict_types = 1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Yapeal\Log;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Stores to any stream resource
 *
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class StreamHandler extends AbstractProcessingHandler
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
        } elseif (is_string($stream)) {
            $this->url = $stream;
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a string.');
        }
        $this->filePermission = $filePermission;
        $this->useLocking = $useLocking;
    }
    /**
     * Closes the handler.
     *
     * This will be called automatically when the object is destroyed
     */
    public function close()
    {
        if ($this->url && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }
    /**
     * Return the currently active stream if it is open.
     *
     * @return resource|null
     */
    public function getStream()
    {
        return $this->stream;
    }
    /**
     * Return the stream URL if it was configured with a URL and not an active resource.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
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
     * Writes the record down to the log of the implementing handler.
     *
     * @param  array $record
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function write(array $record)
    {
        if (!is_resource($this->stream)) {
            if (null === $this->url || '' === $this->url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }
            $this->createDir();
            $this->errorMessage = null;
            set_error_handler([$this, 'customErrorHandler']);
            $this->stream = fopen($this->url, 'a');
            if (null !== $this->filePermission) {
                /** @noinspection PhpUsageOfSilenceOperatorInspection */
                @chmod($this->url, $this->filePermission);
            }
            restore_error_handler();
            if (!is_resource($this->stream)) {
                $this->stream = null;
                $mess = 'The stream or file "%s" could not be opened: ' . $this->errorMessage;
                throw new \UnexpectedValueException(sprintf($mess, $this->url));
            }
        }
        if ($this->useLocking) {
            // Ignoring errors here, there's not much we can do about them and no use blocking either.
            flock($this->stream, LOCK_EX | LOCK_NB);
        }
        fwrite($this->stream, (string)$record['formatted']);
        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
    }
    /**
     * @var int|null $filePermission
     */
    protected $filePermission;
    /**
     * @var resource $stream
     */
    protected $stream;
    /**
     * @var string $url
     */
    protected $url;
    /**
     * @var bool $useLocking
     */
    protected $useLocking;
    /**
     * Tries to create the required directory once.
     *
     * @throws \UnexpectedValueException
     */
    private function createDir()
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated) {
            return;
        }
        $dir = $this->getDirFromStream($this->url);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler([$this, 'customErrorHandler']);
            $status = mkdir($dir, 0777, true);
            restore_error_handler();
            if (false === $status) {
                $mess = 'There is no existing directory at "%s" and its not buildable: ' . $this->errorMessage;
                throw new \UnexpectedValueException(sprintf($mess, $dir));
            }
        }
        $this->dirCreated = true;
    }
    /**
     * @param int    $code
     * @param string $msg
     */
    private function customErrorHandler(int $code, string $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }
    /**
     * @param string $stream
     *
     * @return string|null
     */
    private function getDirFromStream(string $stream)
    {
        if (false === strpos($stream, '://')) {
            return dirname($stream);
        }
        if (0 === strpos($stream, 'file://')) {
            return dirname(substr($stream, 7));
        }
        return null;
    }
    /**
     * @var bool $dirCreated
     */
    private $dirCreated;
    /**
     * @var string $errorMessage
     */
    private $errorMessage;
    /**
     * @var bool $preserve
     */
    private $preserve = true;
}
