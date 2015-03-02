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
        $this->assertEquals(array('FOO' => "b\nar"), $this->Parser->parse('FOO="b\nar" #comment'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::processUnquotedValue
     */
    public function testProcessUnquotedValue()
    {
        $this->assertEquals(9, $this->Parser->processUnquotedValue('9'));
        $this->assertEquals(true, $this->Parser->processUnquotedValue('true'));
        $this->assertEquals(false, $this->Parser->processUnquotedValue('false'));
        $this->assertEquals(null, $this->Parser->processUnquotedValue('null'));
        $this->assertEquals('HI', $this->Parser->processUnquotedValue(' HI '));
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::processQuotedValue
     */
    public function testProcessQuotedValue()
    {
        $this->assertEquals('9', $this->Parser->processQuotedValue('9', array()));
        $this->assertEquals('true', $this->Parser->processQuotedValue('true', array()));
        $this->assertEquals('false', $this->Parser->processQuotedValue('false', array()));
        $this->assertEquals('null', $this->Parser->processQuotedValue('null', array()));
        $this->assertEquals(' HI ', $this->Parser->processQuotedValue(' HI ', array()));

        $this->assertEquals('HI', $this->Parser->processQuotedValue('HI', array()));
        $this->assertEquals("H\nI", $this->Parser->processQuotedValue('H\nI', array()));
        $this->assertEquals('HI$derp', $this->Parser->processQuotedValue('HI$derp', array()));
        $this->assertEquals('HI$derp', $this->Parser->processQuotedValue('HI$derp', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HIderp', $this->Parser->processQuotedValue('HI${derp}', array(
            'derp' => 'derp'
        )));
        $this->assertEquals('HI{}', $this->Parser->processQuotedValue('HI${derp}', array(
        )));
    }
}
