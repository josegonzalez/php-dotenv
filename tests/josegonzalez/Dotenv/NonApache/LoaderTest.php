<?php

namespace josegonzalez\Dotenv\NonApache;

use josegonzalez\Dotenv\Loader;
use PHPUnit_Framework_TestCase;

class LoaderTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
        $this->fixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $this->Loader = new Loader($this->fixturePath . '.env');
    }

    public function tearDown()
    {
        $_ENV = $this->env;
        $_SERVER = $this->server;
        unset($this->env);
        unset($this->server);
        unset($this->fixturePath);
        unset($this->Loader);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     * @expectedException LogicException
     * @expectedExceptionMessage  apache_getenv() and apache_setenv() undefined in non-apache context
     */
    public function testToApacheSetenvExceptionUnavailable()
    {
        if (function_exists('apache_getenv')) {
            $this->markTestSkipped(
                'The apache getenv/setenv functions are available.'
            );
        }

        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
    }
}
