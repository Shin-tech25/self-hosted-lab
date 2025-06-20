<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Worker\Internal;

use OCA\Recognize\Vendor\Amp\Failure;
use OCA\Recognize\Vendor\Amp\Parallel\Sync;
use OCA\Recognize\Vendor\Amp\Parallel\Worker\TaskFailureError;
use OCA\Recognize\Vendor\Amp\Parallel\Worker\TaskFailureException;
use OCA\Recognize\Vendor\Amp\Parallel\Worker\TaskFailureThrowable;
use OCA\Recognize\Vendor\Amp\Promise;
/** @internal */
final class TaskFailure extends TaskResult
{
    const PARENT_EXCEPTION = 0;
    const PARENT_ERROR = 1;
    /** @var string */
    private $type;
    /** @var int */
    private $parent;
    /** @var string */
    private $message;
    /** @var int|string */
    private $code;
    /** @var string[] */
    private $trace;
    /** @var self|null */
    private $previous;
    public function __construct(string $id, \Throwable $exception)
    {
        parent::__construct($id);
        $this->type = \get_class($exception);
        $this->parent = $exception instanceof \Error ? self::PARENT_ERROR : self::PARENT_EXCEPTION;
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        $this->trace = Sync\flattenThrowableBacktrace($exception);
        if ($previous = $exception->getPrevious()) {
            $this->previous = new self($id, $previous);
        }
    }
    public function promise() : Promise
    {
        return new Failure($this->createException());
    }
    private function createException() : TaskFailureThrowable
    {
        $previous = $this->previous ? $this->previous->createException() : null;
        if ($this->parent === self::PARENT_ERROR) {
            return new TaskFailureError($this->type, $this->message, $this->code, $this->trace, $previous);
        }
        return new TaskFailureException($this->type, $this->message, $this->code, $this->trace, $previous);
    }
}
