<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Loader;
use PHPUnit_Framework_TestCase;

$GLOBALS['apache_test_data'] = array();
function apache_getenv($key)
{
    if (isset($GLOBALS['apache_test_data'][$key])) {
        return $GLOBALS['apache_test_data'][$key];
    }
    return false;
}

function apache_setenv($key, $value)
{
    $GLOBALS['apache_test_data'][$key] = $value;
    return true;
}


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
     */
    public function testToApacheSetenv()
    {
        if (!function_exists('apache_getenv') || !function_exists('apache_setenv')) {
            $this->markTestSkipped(
                'The apache getenv/setenv functions are not available.'
            );
        }

        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);

        $this->assertEquals('bar', apache_getenv('FOO'));
        $this->assertEquals('baz', apache_getenv('BAR'));
        $this->assertEquals('with spaces', apache_getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', apache_getenv('EQUALS'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenvSkip()
    {
        if (!function_exists('apache_getenv') || !function_exists('apache_setenv')) {
            $this->markTestSkipped(
                'The apache getenv/setenv functions are not available.'
            );
        }

        $this->Loader->parse();
        $this->Loader->skipExisting('apacheSetenv');
        $this->Loader->apacheSetenv(false);
        $this->Loader->apacheSetenv(false);

        $this->assertEquals('bar', apache_getenv('FOO'));
        $this->assertEquals('baz', apache_getenv('BAR'));
        $this->assertEquals('with spaces', apache_getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', apache_getenv('EQUALS'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     * @expectedException LogicException
     * @expectedExceptionMessage  Key "FOO" has already been defined in apache_getenv()
     */
    public function testToApacheSetenvException()
    {
        if (!function_exists('apache_getenv') || !function_exists('apache_setenv')) {
            $this->markTestSkipped(
                'The apache getenv/setenv functions are not available.'
            );
        }

        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
        $this->Loader->apacheSetenv(false);
    }
}
