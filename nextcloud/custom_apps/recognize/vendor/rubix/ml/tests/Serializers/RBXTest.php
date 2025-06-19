<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Persisters\Serializers;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\RBX;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\AdaBoost;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\Serializer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use stdClass;
use function serialize;
/**
 * @group Serializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Serializers\RBX
 * @internal
 */
class RBXTest extends TestCase
{
    /**
     * @var Persistable
     */
    protected $persistable;
    /**
     * @var RBX
     */
    protected $serializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->serializer = new RBX();
        $this->persistable = new AdaBoost();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RBX::class, $this->serializer);
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
        $this->assertInstanceOf(AdaBoost::class, $persistable);
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
