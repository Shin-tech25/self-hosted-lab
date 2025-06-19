<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Backends;

use OCA\Recognize\Vendor\Rubix\ML\Backends\Serial;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Backend;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Task;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Backends
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Backends\Serial
 * @internal
 */
class SerialTest extends TestCase
{
    /**
     * @var Serial
     */
    protected $backend;
    /**
     * @param int $i
     * @return array<int|float>
     */
    public static function foo(int $i) : array
    {
        return [$i * 2, \microtime(\true)];
    }
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->backend = new Serial();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Serial::class, $this->backend);
        $this->assertInstanceOf(Backend::class, $this->backend);
    }
    /**
     * @test
     */
    public function enqueueProcess() : void
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->backend->enqueue(new Task([self::class, 'foo'], [$i]));
        }
        $results = $this->backend->process();
        $this->assertCount(10, $results);
    }
}
