<?php

namespace OCA\Recognize\Vendor\Amp\Sync;

use OCA\Recognize\Vendor\Amp\CallableMaker;
use OCA\Recognize\Vendor\Amp\Deferred;
use OCA\Recognize\Vendor\Amp\Promise;
use OCA\Recognize\Vendor\Amp\Success;
/** @internal */
class LocalSemaphore implements Semaphore
{
    use CallableMaker;
    // kept for BC only
    /** @var int[] */
    private $locks;
    /** @var Deferred[] */
    private $queue = [];
    public function __construct(int $maxLocks)
    {
        if ($maxLocks < 1) {
            throw new \Error('The number of locks must be greater than 0');
        }
        $this->locks = \range(0, $maxLocks - 1);
    }
    /** {@inheritdoc} */
    public function acquire() : Promise
    {
        if (!empty($this->locks)) {
            return new Success(new Lock(\array_shift($this->locks), \Closure::fromCallable([$this, 'release'])));
        }
        $this->queue[] = $deferred = new Deferred();
        return $deferred->promise();
    }
    private function release(Lock $lock) : void
    {
        $id = $lock->getId();
        if (!empty($this->queue)) {
            $deferred = \array_shift($this->queue);
            $deferred->resolve(new Lock($id, \Closure::fromCallable([$this, 'release'])));
            return;
        }
        $this->locks[] = $id;
    }
}
