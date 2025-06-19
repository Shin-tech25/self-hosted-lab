<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Loggers;

use OCA\Recognize\Vendor\Rubix\ML\Loggers\Screen;
use OCA\Recognize\Vendor\Rubix\ML\Loggers\Logger;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use OCA\Recognize\Vendor\Psr\Log\LoggerInterface;
use OCA\Recognize\Vendor\Psr\Log\LogLevel;
/**
 * @group Loggers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Loggers\Screen
 * @internal
 */
class ScreenTest extends TestCase
{
    /**
     * @var Screen
     */
    protected $logger;
    protected function setUp() : void
    {
        $this->logger = new Screen('default');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Screen::class, $this->logger);
        $this->assertInstanceOf(Logger::class, $this->logger);
        $this->assertInstanceOf(LoggerInterface::class, $this->logger);
    }
    /**
     * @test
     */
    public function log() : void
    {
        $this->expectOutputRegex('/\\b(default.INFO: test)\\b/');
        $this->logger->log(LogLevel::INFO, 'test');
    }
}
