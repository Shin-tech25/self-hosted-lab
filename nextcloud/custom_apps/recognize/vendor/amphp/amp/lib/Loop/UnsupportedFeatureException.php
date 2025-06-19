<?php

namespace OCA\Recognize\Vendor\Amp\Loop;

/**
 * MUST be thrown if a feature is not supported by the system.
 *
 * This might happen if ext-pcntl is missing and the loop driver doesn't support another way to dispatch signals.
 * @internal
 */
class UnsupportedFeatureException extends \Exception
{
}
