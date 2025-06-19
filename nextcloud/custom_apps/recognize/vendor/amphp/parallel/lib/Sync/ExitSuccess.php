<?php

namespace OCA\Recognize\Vendor\Amp\Parallel\Sync;

/** @internal */
final class ExitSuccess implements ExitResult
{
    /** @var mixed */
    private $result;
    public function __construct($result)
    {
        $this->result = $result;
    }
    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        return $this->result;
    }
}
