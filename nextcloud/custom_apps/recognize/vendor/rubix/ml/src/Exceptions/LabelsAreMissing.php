<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Exceptions;

/** @internal */
class LabelsAreMissing extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('A Labeled dataset object is required.');
    }
}
