<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Rubix\ML\Persisters\Persister;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\Serializer;
use Stringable;
use function strlen;
/** @internal */
class Encoding implements Stringable
{
    /**
     * The encoded data.
     *
     * @var string
     */
    protected string $data;
    /**
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }
    /**
     * Return the encoded data.
     *
     * @return string
     */
    public function data() : string
    {
        return $this->data;
    }
    /**
     * Deserialize the encoding with a given serializer and return a persistable object.
     *
     * @param Serializer $serializer
     * @return Persistable
     */
    public function deserializeWith(Serializer $serializer) : Persistable
    {
        return $serializer->deserialize($this);
    }
    /**
     * Save the encoding with a given persister.
     *
     * @param Persister $persister
     */
    public function saveTo(Persister $persister) : void
    {
        $persister->save($this);
    }
    /**
     * Return the size of the encoding in bytes.
     *
     * @return int
     */
    public function bytes() : int
    {
        return strlen($this->data);
    }
    /**
     * Return the object as a string.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->data;
    }
}
