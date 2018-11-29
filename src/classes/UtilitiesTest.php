<?php

use PHPUnit\Framework\TestCase;

final class UtilitiesTest extends TestCase
{
	public function testEnv()
    {
	    $name = "ABCD";
	    $expectedValue = '1234';
		putenv($name . '=' . $expectedValue);
		$this->assertEquals($expectedValue, Utilities::env($name));
	}

	public function testHasValue_WhenExists()
    {
	    $this->assertTrue(Utilities::hasValue(['a' => 1], 'a'));
	}

    public function testHasValue_WhenNotExists()
    {
        $this->assertFalse(Utilities::hasValue(['a' => 1], 'b'));
    }
}
