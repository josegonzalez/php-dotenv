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
        $this->assertSame(array(), $this->Parser->parse("''"));
        $this->assertSame(array(), $this->Parser->parse('""'));
        $this->assertSame(array(), $this->Parser->parse(''));
        $this->assertSame(array(), $this->Parser->parse("\n"));
        $this->assertSame(array(), $this->Parser->parse("#comment\n#comment"));
        $this->assertSame(array(), $this->Parser->parse("FOO\n#comment"));
        $this->assertSame(array('FOO' => ''), $this->Parser->parse("FOO="));
        $this->assertSame(array('FOO' => 'bar'), $this->Parser->parse("FOO='bar'"));
        $this->assertSame(array('FOO' => 'bar'), $this->Parser->parse("FOO=\"bar\""));
        $this->assertSame(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar #comment"));
        $this->assertSame(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar # comment"));
        $this->assertSame(array('FOO' => 'bar'), $this->Parser->parse("FOO=bar  # comment"));
        $this->assertSame(array(), $this->Parser->parse("0FOO=bar # comment"));
        $this->assertSame(array('FOO0' => 'bar'), $this->Parser->parse("FOO0=bar # comment"));
        $this->assertSame(array('FOO' => "b\nar"), $this->Parser->parse('FOO="b\nar" #comment'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::processUnquotedValue
     */
    public function testProcessUnquotedValue()
    {
        $this->assertSame(0, $this->Parser->processUnquotedValue('0'));
        $this->assertSame(9, $this->Parser->processUnquotedValue('9'));
        $this->assertSame(true, $this->Parser->processUnquotedValue('true'));
        $this->assertSame(false, $this->Parser->processUnquotedValue('false'));
        $this->assertSame(null, $this->Parser->processUnquotedValue('null'));
        $this->assertSame('HI', $this->Parser->processUnquotedValue(' HI '));
    }

    /**
     * @covers \josegonzalez\Dotenv\Parser::processQuotedValue
     */
    public function testProcessQuotedValue()
    {
        $this->assertSame('', $this->Parser->processQuotedValue('""', array()));
        $this->assertSame('9', $this->Parser->processQuotedValue('9', array()));
        $this->assertSame('true', $this->Parser->processQuotedValue('true', array()));
        $this->assertSame('false', $this->Parser->processQuotedValue('false', array()));
        $this->assertSame('null', $this->Parser->processQuotedValue('null', array()));
        $this->assertSame(' HI ', $this->Parser->processQuotedValue(' HI ', array()));

        $this->assertSame('HI', $this->Parser->processQuotedValue('HI', array()));
        $this->assertSame("H\nI", $this->Parser->processQuotedValue('H\nI', array()));
        $this->assertSame('HI$derp', $this->Parser->processQuotedValue('HI$derp', array()));
        $this->assertSame('HI$derp', $this->Parser->processQuotedValue('HI$derp', array(
            'derp' => 'derp'
        )));
        $this->assertSame('HIderp', $this->Parser->processQuotedValue('HI${derp}', array(
            'derp' => 'derp'
        )));
        $this->assertSame('HI{}', $this->Parser->processQuotedValue('HI${derp}', array(
        )));
    }
}
