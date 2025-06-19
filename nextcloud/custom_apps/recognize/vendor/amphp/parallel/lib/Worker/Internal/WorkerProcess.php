<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Worker\Internal;

use OCA\Recognize\Vendor\Amp\ByteStream;
use OCA\Recognize\Vendor\Amp\Parallel\Context\Context;
use OCA\Recognize\Vendor\Amp\Parallel\Context\Process;
use OCA\Recognize\Vendor\Amp\Promise;
use function OCA\Recognize\Vendor\Amp\call;
/** @internal */
class WorkerProcess implements Context
{
    /** @var Process */
    private $process;
    public function __construct($script, array $env = [], string $binary = null)
    {
        $this->process = new Process($script, null, $env, $binary);
    }
    public function receive() : Promise
    {
        return $this->process->receive();
    }
    public function send($data) : Promise
    {
        return $this->process->send($data);
    }
    public function isRunning() : bool
    {
        return $this->process->isRunning();
    }
    public function start() : Promise
    {
        return call(function () {
            $result = (yield $this->process->start());
            $stdout = $this->process->getStdout();
            $stdout->unreference();
            $stderr = $this->process->getStderr();
            $stderr->unreference();
            ByteStream\pipe($stdout, ByteStream\getStdout());
            ByteStream\pipe($stderr, ByteStream\getStderr());
            return $result;
        });
    }
    public function kill() : void
    {
        if ($this->process->isRunning()) {
            $this->process->kill();
        }
    }
    public function join() : Promise
    {
        return $this->process->join();
    }
}
