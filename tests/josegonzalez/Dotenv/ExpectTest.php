<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Expect;
use \PHPUnit_Framework_TestCase;

class ExpectTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
    }

    public function tearDown()
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
        $expect = new Expect($this->server);
        $expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required ENV vars missing: ['INVALID']
     */
    public function testExpectRuntimeException()
    {
        $expect = new Expect($this->server);
        $expect('INVALID');
    }

}
