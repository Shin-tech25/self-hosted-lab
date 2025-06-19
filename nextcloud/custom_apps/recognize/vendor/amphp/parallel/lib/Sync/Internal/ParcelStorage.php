<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Sync\Internal;

/** @internal */
final class ParcelStorage extends \Threaded
{
    /** @var mixed */
    private $value;
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }
    /**
     * @param mixed $value
     */
    public function set($value) : void
    {
        $this->value = $value;
    }
}
