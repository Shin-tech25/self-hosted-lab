<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Worker;

/**
 * Interface for factories used to create new workers.
 * @internal
 */
interface WorkerFactory
{
    /**
     * Creates a new worker instance.
     *
     * @return Worker The newly created worker.
     */
    public function create() : Worker;
}
