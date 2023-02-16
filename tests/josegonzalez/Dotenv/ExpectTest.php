<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Expect;
use PHPUnit\Framework\TestCase;

class ExpectTest extends TestCase
{

    /** @var array<mixed, mixed> */
    protected $env = [];
    
    /** @var array<mixed, mixed> */
    protected $server = [];

    public function setUp(): void
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
    }

    public function tearDown(): void
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
    public function testExpect(): void
    {
        $expect = new Expect($this->server);
        $this->assertTrue($expect('USER'));
        $this->assertTrue($expect(['USER', 'HOME']));

        $expect = new Expect($this->server, false);
        $this->assertFalse($expect('FOO'));
        $this->assertFalse($expect(['USER', 'FOO']));
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     */
    public function testExpectLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No arguments were passed to expect()');
        $expect = new Expect($this->server);
        $expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     */
    public function testExpectRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Required ENV vars missing: ['INVALID']");
        $expect = new Expect($this->server);
        $expect('INVALID');
    }
}
