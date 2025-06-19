<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Persisters\Serializers;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\GzipNative;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\Serializer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Serializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Serializers\Gzip
 * @internal
 */
class GzipNativeTest extends TestCase
{
    /**
     * @var Persistable
     */
    protected $persistable;
    /**
     * @var GzipNative
     */
    protected $serializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->serializer = new GzipNative(6);
        $this->persistable = new GaussianNB();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(GzipNative::class, $this->serializer);
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
}
