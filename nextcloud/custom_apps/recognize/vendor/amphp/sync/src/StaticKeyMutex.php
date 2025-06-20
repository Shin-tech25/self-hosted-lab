<?php

namespace OCA\Recognize\Vendor\Amp\Sync;

use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
final class StaticKeyMutex implements Mutex
{
    /** @var KeyedMutex */
    private $mutex;
    /** @var string */
    private $key;
    public function __construct(KeyedMutex $mutex, string $key)
    {
        $this->mutex = $mutex;
        $this->key = $key;
    }
    public function acquire() : Promise
    {
        return $this->mutex->acquire($this->key);
    }
}
