<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Context;

use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
class DefaultContextFactory implements ContextFactory
{
    public function create($script) : Context
    {
        /**
         * Creates a thread if ext-parallel is installed, otherwise creates a child process.
         *
         * @inheritdoc
         */
        if (Parallel::isSupported()) {
            return new Parallel($script);
        }
        return new Process($script);
    }
    /**
     * Creates and starts a thread if ext-parallel is installed, otherwise creates a child process.
     *
     * @inheritdoc
     */
    public function run($script) : Promise
    {
        if (Parallel::isSupported()) {
            return Parallel::run($script);
        }
        return Process::run($script);
    }
}
