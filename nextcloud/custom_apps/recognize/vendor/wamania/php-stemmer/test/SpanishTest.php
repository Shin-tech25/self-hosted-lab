<?php

namespace OCA\Recognize\Vendor\Wamania\Snowball\Tests;

use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use OCA\Recognize\Vendor\Wamania\Snowball\Stemmer\Spanish;
/** @internal */
class SpanishTest extends TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Spanish();
        $snowballStem = $o->stem($word);
        $this->assertEquals($stem, $snowballStem);
    }
    public function load()
    {
        return new CsvFileIterator('test/files/es.txt');
    }
}
