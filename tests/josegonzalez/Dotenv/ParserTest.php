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
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::processReplacements
     */
    public function testProcessReplacements()
    {
        $this->assertEquals('HI', $this->Parser->processReplacements('HI', array()));
        $this->assertEquals('HI$derp', $this->Parser->processReplacements('HI$derp', array()));
        $this->assertEquals('HI$derp', $this->Parser->processReplacements('HI$derp', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HIderp', $this->Parser->processReplacements('HI{$derp}', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HI{}', $this->Parser->processReplacements('HI{$derp}', array(
        )));
    }
}
