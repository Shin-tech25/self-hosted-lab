<?php

namespace OCA\Recognize\Vendor\Amp\ByteStream;

use OCA\Recognize\Vendor\Amp\Promise;
/**
 * An `InputStream` allows reading byte streams in chunks.
 *
 * **Example**
 *
 * ```php
 * function readAll(InputStream $in): Promise {
 *     return Amp\call(function () use ($in) {
 *         $buffer = "";
 *
 *         while (($chunk = yield $in->read()) !== null) {
 *             $buffer .= $chunk;
 *         }
 *
 *         return $buffer;
 *     });
 * }
 * ```
 * @internal
 */
interface InputStream
{
    /**
     * Reads data from the stream.
     *
     * @return Promise Resolves with a string when new data is available or `null` if the stream has closed.
     *
     * @psalm-return Promise<string|null>
     *
     * @throws PendingReadError Thrown if another read operation is still pending.
     */
    public function read() : Promise;
}
