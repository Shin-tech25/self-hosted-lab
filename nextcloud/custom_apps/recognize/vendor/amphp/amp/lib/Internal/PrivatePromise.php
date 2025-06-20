<?php

namespace OCA\Recognize\Vendor\Amp\Internal;

use OCA\Recognize\Vendor\Amp\Promise;
/**
 * Wraps a Promise instance that has public methods to resolve and fail the promise into an object that only allows
 * access to the public API methods.
 * @internal
 */
final class PrivatePromise implements Promise
{
    /** @var Promise */
    private $promise;
    public function __construct(Promise $promise)
    {
        $this->promise = $promise;
    }
    public function onResolve(callable $onResolved)
    {
        $this->promise->onResolve($onResolved);
    }
}
