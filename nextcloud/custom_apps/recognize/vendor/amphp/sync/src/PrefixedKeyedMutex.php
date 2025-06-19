<?php

namespace OCA\Recognize\Vendor\Amp\Sync;

use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
final class PrefixedKeyedMutex implements KeyedMutex
{
    /** @var KeyedMutex */
    private $mutex;
    /** @var string */
    private $prefix;
    public function __construct(KeyedMutex $mutex, string $prefix)
    {
        $this->mutex = $mutex;
        $this->prefix = $prefix;
    }
    public function acquire(string $key) : Promise
    {
        return $this->mutex->acquire($this->prefix . $key);
    }
}
