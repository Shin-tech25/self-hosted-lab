<?php

namespace OCA\Recognize\Vendor\Amp\Sync;

use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
final class PrefixedKeyedSemaphore implements KeyedSemaphore
{
    /** @var KeyedSemaphore */
    private $semaphore;
    /** @var string */
    private $prefix;
    public function __construct(KeyedSemaphore $semaphore, string $prefix)
    {
        $this->semaphore = $semaphore;
        $this->prefix = $prefix;
    }
    public function acquire(string $key) : Promise
    {
        return $this->semaphore->acquire($this->prefix . $key);
    }
}
