<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Loggers;

use function trim;
use function date;
use function strtoupper;
/**
 * Screen
 *
 * A logger that displays log messages to the standard output.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Screen extends Logger
{
    /**
     * The channel name that appears on each line.
     *
     * @var string
     */
    protected string $channel;
    /**
     * The format of the timestamp.
     *
     * @var string
     */
    protected string $timestampFormat;
    /**
     * @param string $channel
     * @param string $timestampFormat
     */
    public function __construct(string $channel = '', string $timestampFormat = 'Y-m-d H:i:s')
    {
        $this->channel = trim($channel);
        $this->timestampFormat = $timestampFormat;
    }
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param mixed[] $context
     */
    public function log($level, $message, array $context = []) : void
    {
        $prefix = '';
        if ($this->timestampFormat) {
            $prefix .= '[' . date($this->timestampFormat) . '] ';
        }
        if ($this->channel) {
            $prefix .= $this->channel . '.';
        }
        $prefix .= strtoupper((string) $level);
        echo $prefix . ': ' . trim($message) . \PHP_EOL;
    }
}
