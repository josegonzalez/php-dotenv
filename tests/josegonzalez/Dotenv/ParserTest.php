<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Parser;
use \PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->Parser = new Parser();
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::parse
     */
    public function testParse()
    {
        $this->assertEquals(array(), $this->Parser->parse(''));
        $this->assertEquals(array(), $this->Parser->parse("\n"));
        $this->assertEquals(array(), $this->Parser->parse("#comment\n#comment"));
        $this->assertEquals(array(), $this->Parser->parse("FOO\n#comment"));
        $this->assertEquals(array('FOO' => ''), $this->Parser->parse("FOO="));
        $this->assertEquals(array('FOO' => 'bar'), $this->Parser->parse("FOO='bar'"));
        $this->assertEquals(array('FOO' => 'bar'), $this->Parser->parse("FOO=\"bar\""));
        $this->assertEquals(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar #comment"));
        $this->assertEquals(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar # comment"));
        $this->assertEquals(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar  # comment"));
        $this->assertEquals(array(), $this->Parser->parse("0FOO=bar # comment"));
        $this->assertEquals(array('FOO0' => 'bar'), $this->Parser->parse("FOO0=bar # comment"));
        $this->assertEquals(array('FOO' => "b\nar"), $this->Parser->parse("FOO=b\\nar #comment"));
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::postProcess
     */
    public function testPostProcess()
    {
        $this->assertEquals('HI', $this->Parser->postProcess('HI', array()));
        $this->assertEquals("H\nI", $this->Parser->postProcess('H\nI', array()));
        $this->assertEquals('HI$derp', $this->Parser->postProcess('HI$derp', array()));
        $this->assertEquals('HI$derp', $this->Parser->postProcess('HI$derp', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HIderp', $this->Parser->postProcess('HI${derp}', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HI{}', $this->Parser->postProcess('HI${derp}', array(
        )));
    }
}
