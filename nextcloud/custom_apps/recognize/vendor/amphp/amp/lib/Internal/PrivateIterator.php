<?php

namespace OCA\Recognize\Vendor\Amp\Internal;

use OCA\Recognize\Vendor\Amp\Iterator;
use OCA\Recognize\Vendor\Amp\Promise;
/**
 * Wraps an Iterator instance that has public methods to emit, complete, and fail into an object that only allows
 * access to the public API methods.
 *
 * @template-covariant TValue
 * @template-implements Iterator<TValue>
 * @internal
 */
final class PrivateIterator implements Iterator
{
    /** @var Iterator<TValue> */
    private $iterator;
    /**
     * @param Iterator $iterator
     *
     * @psalm-param Iterator<TValue> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }
    /**
     * @return Promise<bool>
     */
    public function advance() : Promise
    {
        return $this->iterator->advance();
    }
    /**
     * @psalm-return TValue
     */
    public function getCurrent()
    {
        return $this->iterator->getCurrent();
    }
}
