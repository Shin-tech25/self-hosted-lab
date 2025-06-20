<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Backends;

use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Task;
use Stringable;
/**
 * Backend
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Backend extends Stringable
{
    /**
     * Queue up a task for backend processing.
     *
     * @internal
     *
     * @param Task $task
     * @param callable(mixed,mixed):void $after
     * @param mixed $context
     */
    public function enqueue(Task $task, ?callable $after = null, $context = null) : void;
    /**
     * Process the queue and return the results.
     *
     * @internal
     *
     * @return mixed[]
     */
    public function process() : array;
    /**
     * Flush the queue.
     *
     * @internal
     */
    public function flush() : void;
}
