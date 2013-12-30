<?php

use josegonzalez\Dotenv\Load;
use \PHPUnit_Framework_TestCase;

class LoadTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->env = $_ENV;
		$this->server = $_SERVER;
		$this->fixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
		$this->Loader = new Load($this->fixturePath . '.env');
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

	public function testFilepath()
	{
		$this->assertEquals($this->fixturePath . '.env', $this->Loader->filepath());
	}

	public function testSetFilepath()
	{
		$this->Loader->setFilepath('/tmp/.env');
		$this->assertEquals('/tmp/.env', $this->Loader->filepath());
	}

	public function testParse()
	{
		$this->Loader->parse();
		$environment = $this->Loader->toArray();

		$this->assertEquals('bar', $environment['FOO']);
		$this->assertEquals('baz', $environment['BAR']);
		$this->assertEquals('with spaces', $environment['SPACED']);
		$this->assertEquals('pgsql:host=localhost;dbname=test', $environment['EQUALS']);
		$this->assertEquals('', $environment['NULL']);
	}

	public function testParseExported()
	{
		$this->Loader->setFilepath($this->fixturePath . 'exported.env');
		$this->Loader->parse();
		$environment = $this->Loader->toArray();

		$this->assertEquals('bar', $environment['EFOO']);
		$this->assertEquals('baz', $environment['EBAR']);
		$this->assertEquals('with spaces', $environment['ESPACED']);
		$this->assertEquals('pgsql:host=localhost;dbname=test', $environment['EEQUALS']);
		$this->assertEquals('', $environment['ENULL']);
	}

	public function testParseQuoted()
	{
		$this->Loader->setFilepath($this->fixturePath . 'quoted.env');
		$this->Loader->parse();
		$environment = $this->Loader->toArray();

		$this->assertEquals('bar', $environment['QFOO']);
		$this->assertEquals('baz', $environment['QBAR']);
		$this->assertEquals('with spaces', $environment['QSPACED']);
		$this->assertEquals('pgsql:host=localhost;dbname=test', $environment['QEQUALS']);
		$this->assertEquals('', $environment['QNULL']);
	}

	public function testExpect()
	{
		$this->Loader->parse();
		$this->assertInstanceOf('josegonzalez\Dotenv\Load', $this->Loader->expect('FOO'));
		$this->assertInstanceOf('josegonzalez\Dotenv\Load', $this->Loader->expect(array('FOO', 'BAR')));
	}

	public function testDefine()
	{
		$this->Loader->parse();
		$this->Loader->define();

		$this->assertEquals('bar', FOO);
		$this->assertEquals('baz', BAR);
		$this->assertEquals('with spaces', SPACED);
		$this->assertEquals('pgsql:host=localhost;dbname=test', EQUALS);
	}

	public function testToEnv()
	{
		$this->Loader->parse();
		$this->Loader->toEnv();

		$this->assertEquals('bar', $_ENV['FOO']);
		$this->assertEquals('baz', $_ENV['BAR']);
		$this->assertEquals('with spaces', $_ENV['SPACED']);
		$this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['EQUALS']);
		$this->assertEquals('', $_ENV['NULL']);
	}

	public function testToServer()
	{
		$this->Loader->parse();
		$this->Loader->toServer();

		$this->assertEquals('bar', $_SERVER['FOO']);
		$this->assertEquals('baz', $_SERVER['BAR']);
		$this->assertEquals('with spaces', $_SERVER['SPACED']);
		$this->assertEquals('pgsql:host=localhost;dbname=test', $_SERVER['EQUALS']);
		$this->assertEquals('', $_SERVER['NULL']);
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Environment must be parsed before calling expect()
	 */
	public function testExpectRequireException()
	{
		$this->Loader->expect();
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage No arguments were passed to expect()
	 */
	public function testExpectArgumentException()
	{
		$this->Loader->parse();
		$this->Loader->expect();
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Required ENV vars missing: ['BAZ']
	 */
	public function testExpectMissingRequiredException()
	{
		$this->Loader->parse();
		$this->assertTrue($this->Loader->expect(array('BAZ')));
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Environment must be parsed before calling define()
	 */
	public function testDefineRequireException()
	{
		$this->Loader->define();
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Key "FOO" has already been defined
	 */
	public function testDefineNullException()
	{
		$this->Loader->parse();
		$this->Loader->define();
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Environment must be parsed before calling toEnv()
	 */
	public function testToEnvRequireException()
	{
		$this->Loader->toEnv();
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Environment must be parsed before calling toServer()
	 */
	public function testToServerRequireException()
	{
		$this->Loader->toServer();
	}

	/**
	 * @expectedException LogicException
	 * @expectedExceptionMessage Environment must be parsed before calling toArray()
	 */
	public function testToArrayException()
	{
		$this->Loader->toArray();
	}
}
