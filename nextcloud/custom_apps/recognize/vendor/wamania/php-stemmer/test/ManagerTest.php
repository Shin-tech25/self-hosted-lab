<?php

namespace OCA\Recognize\Vendor\Wamania\Snowball\Tests;

use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use OCA\Recognize\Vendor\Wamania\Snowball\StemmerManager;
/** @internal */
class ManagerTest extends TestCase
{
    public function testManager()
    {
        $stemmerManager = new StemmerManager();
        $this->assertEquals('anticonstitutionnel', $stemmerManager->stem('anticonstitutionnelement', 'fr'));
    }
}
