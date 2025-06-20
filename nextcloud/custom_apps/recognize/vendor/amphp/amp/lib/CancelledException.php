<?php

namespace OCA\Recognize\Vendor\Amp;

/**
 * Will be thrown in case an operation is cancelled.
 *
 * @see CancellationToken
 * @see CancellationTokenSource
 * @internal
 */
class CancelledException extends \Exception
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct("The operation was cancelled", 0, $previous);
    }
}
