<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Worker;

use OCA\Recognize\Vendor\Amp\Parallel\Context\Parallel;
/**
 * A worker parallel extension thread that executes task objects.
 * @internal
 */
final class WorkerParallel extends TaskWorker
{
    const SCRIPT_PATH = __DIR__ . "/Internal/worker-process.php";
    /**
     * @param string $envClassName Name of class implementing \Amp\Parallel\Worker\Environment to instigate.
     *     Defaults to \Amp\Parallel\Worker\BasicEnvironment.
     * @param string|null Path to custom bootstrap file.
     *
     * @throws \Error If the PHP binary path given cannot be found or is not executable.
     */
    public function __construct(string $envClassName = BasicEnvironment::class, string $bootstrapPath = null)
    {
        $script = [self::SCRIPT_PATH, $envClassName];
        if ($bootstrapPath !== null) {
            $script[] = $bootstrapPath;
        }
        parent::__construct(new Parallel($script));
    }
}
