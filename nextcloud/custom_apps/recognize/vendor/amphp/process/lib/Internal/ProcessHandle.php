<?php

namespace OCA\Recognize\Vendor\Amp\Process\Internal;

use OCA\Recognize\Vendor\Amp\Deferred;
use OCA\Recognize\Vendor\Amp\Process\ProcessInputStream;
use OCA\Recognize\Vendor\Amp\Process\ProcessOutputStream;
use OCA\Recognize\Vendor\Amp\Struct;
/** @internal */
abstract class ProcessHandle
{
    use Struct;
    /** @var ProcessOutputStream */
    public $stdin;
    /** @var ProcessInputStream */
    public $stdout;
    /** @var ProcessInputStream */
    public $stderr;
    /** @var Deferred */
    public $pidDeferred;
    /** @var int */
    public $status = ProcessStatus::STARTING;
}
