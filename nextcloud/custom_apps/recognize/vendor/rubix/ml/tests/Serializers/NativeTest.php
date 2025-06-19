<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Persisters\Serializers;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\Native;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\Serializer;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use stdClass;
use function serialize;
/**
 * @group Serializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Serializers\Native
 * @internal
 */
class NativeTest extends TestCase
{
    /**
     * @var Persistable
     */
    protected $persistable;
    /**
     * @var Native
     */
    protected $serializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->serializer = new Native();
        $this->persistable = new GaussianNB();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Native::class, $this->serializer);
        $this->assertInstanceOf(Serializer::class, $this->serializer);
    }
    /**
     * @test
     */
    public function serializeDeserialize() : void
    {
        $data = $this->serializer->serialize($this->persistable);
        $this->assertInstanceOf(Encoding::class, $data);
        $persistable = $this->serializer->deserialize($data);
        $this->assertInstanceOf(GaussianNB::class, $persistable);
        $this->assertInstanceOf(Persistable::class, $persistable);
    }
    /**
     * @return array<mixed>
     */
    public function deserializeInvalidData() : array
    {
        return [[3], [new stdClass()]];
    }
    /**
     * @test
     *
     * @param mixed $obj
     *
     * @dataProvider deserializeInvalidData
     */
    public function deserializeBadData($obj) : void
    {
        $data = new Encoding(serialize($obj));
        $this->expectException(RuntimeException::class);
        $this->serializer->deserialize($data);
    }
}
