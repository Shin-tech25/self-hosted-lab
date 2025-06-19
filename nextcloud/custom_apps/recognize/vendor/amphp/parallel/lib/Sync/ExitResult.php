<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Sync;

/** @internal */
interface ExitResult
{
    /**
     * @return mixed Return value of the callable given to the execution context.
     *
     * @throws \Amp\Parallel\Sync\PanicError If the context exited with an uncaught exception.
     */
    public function getResult();
}
