<?php

namespace OCA\Recognize\Vendor\Amp\Serialization;

/**
 * @param string $data Binary data.
 *
 * @return string Unprintable characters encoded as \x##.
 * @internal
 */
function encodeUnprintableChars(string $data) : string
{
    return \preg_replace_callback("/[^ -~]/", function (array $matches) : string {
        return "\\x" . \dechex(\ord($matches[0]));
    }, $data);
}
