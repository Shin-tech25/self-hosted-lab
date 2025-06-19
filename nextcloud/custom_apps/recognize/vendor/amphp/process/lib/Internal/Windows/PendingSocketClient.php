<?php

namespace OCA\Recognize\Vendor\Amp\Process\Internal\Windows;

use OCA\Recognize\Vendor\Amp\Struct;
/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class PendingSocketClient
{
    use Struct;
    public $readWatcher;
    public $timeoutWatcher;
    public $receivedDataBuffer = '';
    public $pid;
    public $streamId;
}
