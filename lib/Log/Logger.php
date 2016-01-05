<?php
/**
 * Contains Logger class.
 *
 * PHP version 5.5
 *
 * @copyright 2015 Michael Cummings
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Log;

use Monolog\Logger as MLogger;
use Yapeal\Event\LogEventInterface;

/**
 * Class Logger
 */
class Logger extends MLogger implements EventAwareLoggerInterface
{
    /**
     * @param LogEventInterface $event
     */
    public function logEvent(LogEventInterface $event)
    {
        $this->log(
            $event->getLevel(),
            $event->getMessage(),
            $event->getContext()
        );
    }
}
