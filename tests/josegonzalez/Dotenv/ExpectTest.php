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
     * Phpunit >= 8.0 uses setUp(): void which this needs to match, but will break php 5.x
     */
    public function compatibleSetUp()
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
    }

    /**
     * Hopefully this will allow php > 7.1 to run.
     * Phpunit >= 8.0 uses tearDown(): void which this needs to match, but will break php 5.x
     */
    public function compatibleTearDown()
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
        $this->compatibleSetUp();
        $expect = new Expect($this->server);
        $this->assertTrue($expect('USER'));
        $this->assertTrue($expect(array('USER', 'HOME')));

        $expect = new Expect($this->server, false);
        $this->assertFalse($expect('FOO'));
        $this->assertFalse($expect(array('USER', 'FOO')));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @expectedException LogicException
     * @expectedExceptionMessage No arguments were passed to expect()
     */
    public function testExpectLogicException()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('No arguments were passed to expect()');
        }
        $expect = new Expect($this->server);
        $expect();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required ENV vars missing: ['INVALID']
     */
    public function testExpectRuntimeException()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("Required ENV vars missing: ['INVALID']");
        }
        $expect = new Expect($this->server);
        $expect('INVALID');
        $this->compatibleTearDown();
    }
}
