<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Expect;
use \PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class ExpectTest extends PHPUnit_Framework_TestCase
{
    protected $env = array();

    protected $server = array();

    /**
     * Hopefully this will allow php > 7.1 to run.
     * Phpunit >= *.0 uses setUp(): void which this needs to match, but will break php 5.x
     */
    public function _setUp()
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
    }

    /**
     * Hopefully this will allow php > 7.1 to run.
     * Phpunit >= *.0 uses tearDown(): void which this needs to match, but will break php 5.x
     */
    public function _tearDown()
    {
        $_ENV = $this->env;
        $_SERVER = $this->server;
        unset($this->env);
        unset($this->server);
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     */
    public function testExpect()
    {
        $this->_setUp();
        $expect = new Expect($this->server);
        $this->assertTrue($expect('USER'));
        $this->assertTrue($expect(array('USER', 'HOME')));

        $expect = new Expect($this->server, false);
        $this->assertFalse($expect('FOO'));
        $this->assertFalse($expect(array('USER', 'FOO')));
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @expectedException LogicException
     * @expectedExceptionMessage No arguments were passed to expect()
     */
    public function testExpectLogicException()
    {
        $this->_setUp();
        $expect = new Expect($this->server);
        $expect();
        $this->_tearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required ENV vars missing: ['INVALID']
     */
    public function testExpectRuntimeException()
    {
        $this->_setUp();
        $expect = new Expect($this->server);
        $expect('INVALID');
        $this->_tearDown();
    }
}
