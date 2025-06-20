<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Context;

use OCA\Recognize\Vendor\Amp\Loop;
use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
const LOOP_FACTORY_IDENTIFIER = ContextFactory::class;
/**
 * @param string|string[] $script Path to PHP script or array with first element as path and following elements options
 *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
 *
 * @return Context
 * @internal
 */
function create($script) : Context
{
    return factory()->create($script);
}
/**
 * Creates and starts a process based on installed extensions (a thread if ext-parallel is installed, otherwise a child
 * process).
 *
 * @param string|string[] $script Path to PHP script or array with first element as path and following elements options
 *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
 *
 * @return Promise<Context>
 * @internal
 */
function run($script) : Promise
{
    return factory()->run($script);
}
/**
 * Gets or sets the global context factory.
 *
 * @param ContextFactory|null $factory
 *
 * @return ContextFactory
 * @internal
 */
function factory(?ContextFactory $factory = null) : ContextFactory
{
    if ($factory === null) {
        $factory = Loop::getState(LOOP_FACTORY_IDENTIFIER);
        if ($factory) {
            return $factory;
        }
        $factory = new DefaultContextFactory();
    }
    Loop::setState(LOOP_FACTORY_IDENTIFIER, $factory);
    return $factory;
}
