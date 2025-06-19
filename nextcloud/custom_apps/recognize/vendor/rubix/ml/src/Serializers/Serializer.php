<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Serializers;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use Stringable;
/**
 * Serializer
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Serializer extends Stringable
{
    /**
     * Serialize a persistable object and return the data.
     *
     * @internal
     *
     * @param Persistable $persistable
     * @return Encoding
     */
    public function serialize(Persistable $persistable) : Encoding;
    /**
     * Deserialize a persistable object and return it.
     *
     * @internal
     *
     * @param Encoding $encoding
     * @throws \OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException
     * @return Persistable
     */
    public function deserialize(Encoding $encoding) : Persistable;
}
