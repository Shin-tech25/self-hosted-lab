<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Persisters;

use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Persisters\Persister;
use OCA\Recognize\Vendor\Rubix\ML\Persisters\Filesystem;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Persisters
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Persisters\Filesystem
 * @internal
 */
class FilesystemTest extends TestCase
{
    protected const PATH = __DIR__ . '/test.model';
    /**
     * @var Filesystem
     */
    protected $persister;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->persister = new Filesystem(self::PATH, \true);
    }
    protected function assertPreConditions() : void
    {
        $this->assertFileDoesNotExist(self::PATH);
    }
    /**
     * @after
     */
    protected function tearDown() : void
    {
        if (\file_exists(self::PATH)) {
            \unlink(self::PATH);
        }
        foreach (\glob(self::PATH . '*.old') ?: [] as $filename) {
            \unlink($filename);
        }
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Filesystem::class, $this->persister);
        $this->assertInstanceOf(Persister::class, $this->persister);
    }
    /**
     * @test
     */
    public function saveLoad() : void
    {
        $encoding = new Encoding("Bitch, I'm for real!");
        $this->persister->save($encoding);
        $this->assertFileExists(self::PATH);
        $encoding = $this->persister->load();
        $this->assertInstanceOf(Encoding::class, $encoding);
    }
}
