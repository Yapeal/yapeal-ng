<?php
/**
 * LogEventInterface.php
 *
 * PHP version 5.5
 *
 * @since  20150104 14:09
 * @author Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use EventMediator\EventInterface;

/**
 * Interface LogEventInterface
 */
interface LogEventInterface extends EventInterface
{
    /**
     * @return array
     */
    public function getContext();
    /**
     * @return mixed
     */
    public function getLevel();
    /**
     * @return string
     */
    public function getMessage();
    /**
     * @param array $value
     *
     * @return self Fluent interface.
     */
    public function setContext(array $value);
    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setLevel($value);
    /**
     * @param string $value
     *
     * @return self Fluent interface.
     */
    public function setMessage($value);
}
