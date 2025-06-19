<?php

namespace OCA\Recognize\Vendor\Wamania\Snowball\Tests;

use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Italian;
/** @internal */
class ItalianTest extends TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Italian();
        $snowballStem = $o->stem($word);
        $this->assertEquals($stem, $snowballStem);
    }
    public function load()
    {
        return new CsvFileIterator('test/files/it.txt');
    }
}
