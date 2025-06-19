<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Psr\Log\LoggerInterface;
use OCA\Recognize\Vendor\Psr\Log\LoggerAwareInterface;
/**
 * Verbose
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Verbose extends LoggerAwareInterface
{
    /**
     * Return the logger or null if not set.
     *
     * @return LoggerInterface|null
     */
    public function logger() : ?LoggerInterface;
}
