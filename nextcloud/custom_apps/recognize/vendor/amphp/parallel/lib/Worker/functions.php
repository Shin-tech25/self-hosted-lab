<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Worker;

use OCA\Recognize\Vendor\Amp\Loop;
use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
const LOOP_POOL_IDENTIFIER = Pool::class;
/** @internal */
const LOOP_FACTORY_IDENTIFIER = WorkerFactory::class;
/**
 * Gets or sets the global worker pool.
 *
 * @param Pool|null $pool A worker pool instance.
 *
 * @return Pool The global worker pool instance.
 * @internal
 */
function pool(Pool $pool = null) : Pool
{
    if ($pool === null) {
        $pool = Loop::getState(LOOP_POOL_IDENTIFIER);
        if ($pool) {
            return $pool;
        }
        $pool = new DefaultPool();
    }
    Loop::setState(LOOP_POOL_IDENTIFIER, $pool);
    return $pool;
}
/**
 * Enqueues a task to be executed by the global worker pool.
 *
 * @param Task $task The task to enqueue.
 *
 * @return Promise<mixed>
 * @internal
 */
function enqueue(Task $task) : Promise
{
    return pool()->enqueue($task);
}
/**
 * Enqueues a callable to be executed by the global worker pool.
 *
 * @param callable $callable Callable needs to be serializable.
 * @param mixed    ...$args Arguments have to be serializable.
 *
 * @return Promise<mixed>
 * @internal
 */
function enqueueCallable(callable $callable, ...$args)
{
    return enqueue(new CallableTask($callable, $args));
}
/**
 * Gets a worker from the global worker pool.
 *
 * @return \Amp\Parallel\Worker\Worker
 * @internal
 */
function worker() : Worker
{
    return pool()->getWorker();
}
/**
 * Creates a worker using the global worker factory.
 *
 * @return \Amp\Parallel\Worker\Worker
 * @internal
 */
function create() : Worker
{
    return factory()->create();
}
/**
 * Gets or sets the global worker factory.
 *
 * @param WorkerFactory|null $factory
 *
 * @return WorkerFactory
 * @internal
 */
function factory(WorkerFactory $factory = null) : WorkerFactory
{
    if ($factory === null) {
        $factory = Loop::getState(LOOP_FACTORY_IDENTIFIER);
        if ($factory) {
            return $factory;
        }
        $factory = new DefaultWorkerFactory();
    }
    Loop::setState(LOOP_FACTORY_IDENTIFIER, $factory);
    return $factory;
}
