<?php

namespace OCA\Recognize\Vendor\Amp;

/**
 * Thrown if a promise doesn't resolve within a specified timeout.
 *
 * @see \Amp\Promise\timeout()
 * @internal
 */
class TimeoutException extends \Exception
{
    /**
     * @param string $message Exception message.
     */
    public function __construct(string $message = "Operation timed out")
    {
        parent::__construct($message);
    }
}
