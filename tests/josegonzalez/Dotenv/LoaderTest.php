<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Loader;
use \PHPUnit_Framework_TestCase;

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
     * @covers \josegonzalez\Dotenv\Loader::filepath
     */
    public function testFilepath()
    {
        $this->assertEquals($this->fixturePath . '.env', $this->Loader->filepath());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     */
    public function testSetFilepath()
    {
        $this->Loader->setFilepath('/tmp/.env');
        $this->assertEquals('/tmp/.env', $this->Loader->filepath());

        $this->Loader->setFilepath(null);
        $basePath = realpath(implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..',
            'src' . DIRECTORY_SEPARATOR . 'josegonzalez' . DIRECTORY_SEPARATOR . 'Dotenv',
        )));
        $this->assertEquals($basePath . DIRECTORY_SEPARATOR .'.env', $this->Loader->filepath());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParse()
    {
        $this->Loader->setFilepath($this->fixturePath . 'all.env');
        $this->Loader->parse();
        $environment = $this->Loader->toArray();
        $this->assertEquals('bar', $environment['FOO']);
        $this->assertEquals('baz', $environment['BAR']);
        $this->assertEquals('unquotedwithspaces', $environment['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test#notacomment', $environment['EQUALS']);

        $this->assertEquals('bar', $environment['EFOO']);
        $this->assertEquals('baz', $environment['EBAR']);
        $this->assertEquals('with spaces', $environment['ESPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $environment['EEQUALS']);

        $this->assertEquals('bar', $environment['QFOO']);
        $this->assertEquals('baz', $environment['QBAR']);
        $this->assertEquals('with spaces', $environment['QSPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $environment['QEQUALS']);

        $this->assertEquals('bar', $environment['SFOO']);
        $this->assertEquals('baz', $environment['SBAR']);
        $this->assertEquals('with spaces', $environment['SSPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $environment['SEQUALS']);

        $this->assertEquals('bar', $environment['CFOO']);
        $this->assertEquals('with spaces', $environment['CSPACED']);
        $this->assertEquals('a value with a # character', $environment['CQUOTES']);
        $this->assertEquals('a value with a # character & a quote " character inside quotes', $environment['CQUOTESWITHQUOTE']);
        $this->assertEquals('', $environment['CNULL']);

        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'unquotedwithspaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test#notacomment',
            'ANOTHER_NEWLINE' => "quoted newline\nchar",
            'NOT_SKIPPED1' => 'not skipped',
            'EFOO' => 'bar',
            'EBAR' => 'baz',
            'ESPACED' => 'with spaces',
            'EEQUALS' => 'pgsql:host=localhost;dbname=test',
            'QFOO' => 'bar',
            'QBAR' => 'baz',
            'QSPACED' => 'with spaces',
            'QEQUALS' => 'pgsql:host=localhost;dbname=test',
            'SFOO' => 'bar',
            'SBAR' => 'baz',
            'SSPACED' => 'with spaces',
            'SEQUALS' => 'pgsql:host=localhost;dbname=test',
            'CFOO' => 'bar',
            'CSPACED' => 'with spaces',
            'CQUOTES' => 'a value with a # character',
            'CQUOTESWITHQUOTE' => 'a value with a # character & a quote " character inside quotes',
            'CNULL' => '',
            'SPVAR1' => '$a6^C7k%zs+e^.jvjXk',
            'SPVAR2' => '?BUty3koaV3%GA*hMAwH}B',
            'SPVAR3' => 'jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r',
            'SPVAR4' => '22222:22#2^{',
            'SPVAR5' => 'test some escaped characters like a quote " or maybe a backslash \\\\',
            'NVAR1' => 'Hello',
            'NVAR2' => 'World!',
            'NVAR3' => 'Hello World!',
            'NVAR4' => '{$NVAR1} {$NVAR2}',
            'NVAR5' => '$NVAR1 {NVAR2}',
            'PHP_NULL' => null,
            'STRING_NULL' => 'null',
            'PHP_TRUE' => true,
            'STRING_TRUE' => 'true',
            'PHP_FALSE' => false,
            'STRING_FALSE' => 'false',
        ), $environment);

        $this->Loader->setFilepath($this->fixturePath . 'cake.env');
        $this->Loader->parse();
        $environment = $this->Loader->toArray();
        $this->assertEquals('app', $environment['APP_NAME']);
        $this->assertEquals('2', $environment['DEBUG']);
        $this->assertEquals('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi', $environment['SECURITY_SALT']);
        $this->assertEquals('76859309657453542496749683645', $environment['SECURITY_CIPHER_SEED']);
        $this->assertEquals('mysql://user:password@localhost/database_name?encoding=utf8', $environment['DATABASE_URL']);
        $this->assertEquals('mysql://user:password@localhost/test_database_name?encoding=utf8', $environment['DATABASE_TEST_URL']);
        $this->assertEquals('file:///CACHE/?prefix=APP_NAME_&duration=DURATION', $environment['CACHE_URL']);
        $this->assertEquals('file:///CACHE/?prefix=APP_NAME_debug_kit_&duration=DURATION', $environment['CACHE_DEBUG_KIT_URL']);
        $this->assertEquals('file:///CACHE/?prefix=APP_NAME_cake_core_&duration=DURATION', $environment['CACHE_CAKE_CORE_URL']);
        $this->assertEquals('file:///CACHE/?prefix=APP_NAME_cake_model_&duration=DURATION', $environment['CACHE_CAKE_MODEL_URL']);
        $this->assertEquals('file:///LOGS/?types=notice,info,debug&file=debug', $environment['LOG_URL']);
        $this->assertEquals('file:///LOGS/?types=warning,error,critical,alert,emergency&file=error', $environment['LOG_ERROR_URL']);
        $this->assertEquals('mail://localhost/?from=you@localhost', $environment['EMAIL_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/?from[site@localhost]=My+Site&timeout=30', $environment['EMAIL_SMTP_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/?from=you@localhost&messageId=1&template=0&layout=0&timeout=30', $environment['EMAIL_FAST_URL']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException LogicException
     * @expectedExceptionMessage Environment file '.env' is not found
     */
    public function testParseFileNotFound()
    {
        $this->Loader->setFilepath('.env');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException LogicException
     * @expectedExceptionMessage Environment file '/tmp' is a directory. Should be a file
     */
    public function testParseFileIsDirectory()
    {
        $this->Loader->setFilepath('/tmp');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     */
    public function testExpect()
    {
        $this->Loader->parse();
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect('FOO'));
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect(array('FOO', 'BAR')));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling expect()
     */
    public function testExpectRequireParse()
    {
        $this->Loader->expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     * @expectedException LogicException
     * @expectedExceptionMessage No arguments were passed to expect()
     */
    public function testExpectLogicException()
    {
        $this->Loader->parse();
        $this->Loader->expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     * @expectedException RuntimeException
     * @expectedExceptionMessage Required ENV vars missing: ['INVALID']
     */
    public function testExpectRuntimeException()
    {
        $this->Loader->parse();
        $this->Loader->expect('INVALID');
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::define
     */
    public function testDefine()
    {
        $this->Loader->parse();
        $this->Loader->define();

        $this->assertEquals('bar', FOO);
        $this->assertEquals('baz', BAR);
        $this->assertEquals('with spaces', SPACED);
        $this->assertEquals('pgsql:host=localhost;dbname=test', EQUALS);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::define
     */
    public function testDefineSkip()
    {
        $this->Loader->parse();
        $this->Loader->skipExisting('define');
        $this->Loader->define();

        $this->assertEquals('bar', FOO);
        $this->assertEquals('baz', BAR);
        $this->assertEquals('with spaces', SPACED);
        $this->assertEquals('pgsql:host=localhost;dbname=test', EQUALS);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::define
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined
     */
    public function testDefineException()
    {
        $this->Loader->parse();
        $this->Loader->define();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnv()
    {
        $this->Loader->parse();
        $this->Loader->toEnv(false);

        $this->assertEquals('bar', $_ENV['FOO']);
        $this->assertEquals('baz', $_ENV['BAR']);
        $this->assertEquals('with spaces', $_ENV['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['EQUALS']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnvSkip()
    {
        $this->Loader->parse();
        $this->Loader->skipExisting('toEnv');
        $this->Loader->toEnv(false);
        $this->Loader->toEnv(false);

        $this->assertEquals('bar', $_ENV['FOO']);
        $this->assertEquals('baz', $_ENV['BAR']);
        $this->assertEquals('with spaces', $_ENV['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['EQUALS']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in $_ENV
     */
    public function testToEnvException()
    {
        $this->Loader->parse();
        $this->Loader->toEnv(false);
        $this->Loader->toEnv(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenv()
    {
        $this->Loader->parse();
        $this->Loader->putenv(false);

        $this->assertEquals('bar', getenv('FOO'));
        $this->assertEquals('baz', getenv('BAR'));
        $this->assertEquals('with spaces', getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', getenv('EQUALS'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenvSkip()
    {
        $this->Loader->parse();
        $this->Loader->skipExisting('putenv');
        $this->Loader->putenv(false);
        $this->Loader->putenv(false);

        $this->assertEquals('bar', getenv('FOO'));
        $this->assertEquals('baz', getenv('BAR'));
        $this->assertEquals('with spaces', getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', getenv('EQUALS'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     * @expectedException LogicException
     * @expectedExceptionMessage  Key "FOO" has already been defined in getenv()
     */
    public function testToPutenvException()
    {
        $this->Loader->parse();
        $this->Loader->putenv(false);
        $this->Loader->putenv(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServer()
    {
        $this->Loader->parse();
        $this->Loader->toServer(false);

        $this->assertEquals('bar', $_SERVER['FOO']);
        $this->assertEquals('baz', $_SERVER['BAR']);
        $this->assertEquals('with spaces', $_SERVER['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_SERVER['EQUALS']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServerSkip()
    {
        $this->Loader->parse();
        $this->Loader->skipExisting('toServer');
        $this->Loader->toServer(false);
        $this->Loader->toServer(false);

        $this->assertEquals('bar', $_SERVER['FOO']);
        $this->assertEquals('baz', $_SERVER['BAR']);
        $this->assertEquals('with spaces', $_SERVER['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_SERVER['EQUALS']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toServer
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in $_SERVER
     */
    public function testToServerException()
    {
        $this->Loader->parse();
        $this->Loader->toServer(false);
        $this->Loader->toServer(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::skipped
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     */
    public function testSkipExisting()
    {
        $this->assertEquals(array(), $this->Loader->skipped());

        $this->Loader->skipExisting('toEnv');
        $this->assertEquals(array('toEnv'), $this->Loader->skipped());

        $this->Loader->skipExisting(array('toEnv'));
        $this->assertEquals(array('toEnv'), $this->Loader->skipped());

        $this->Loader->skipExisting();
        $this->assertEquals(array('define', 'toEnv', 'toServer', 'putenv'), $this->Loader->skipped());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::prefix
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     */
    public function testPrefix()
    {
        $this->assertEquals('KEY', $this->Loader->prefixed('KEY'));

        $this->Loader->prefix('PREFIX_');
        $this->assertEquals('PREFIX_KEY', $this->Loader->prefixed('KEY'));

        $this->Loader->prefix('PREFIX_TWO_');
        $this->assertEquals('PREFIX_TWO_KEY', $this->Loader->prefixed('KEY'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toArray
     */
    public function testToArray()
    {
        $this->Loader->parse();
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling toArray()
     */
    public function testToArrayRequireParse()
    {
        $this->Loader->toArray();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__toString
     */
    public function testToString()
    {
        $this->assertEquals('[]', $this->Loader->__toString());

        $this->Loader->parse();
        $this->assertEquals('{"FOO":"bar","BAR":"baz","SPACED":"with spaces","EQUALS":"pgsql:host=localhost;dbname=test"}', $this->Loader->__toString());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     */
    public function testRequireParse()
    {
        $this->Loader->parse();
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling toEnv()
     */
    public function testRequireParseException()
    {
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     */
    public function testRequireParseNoException()
    {
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage derp
     */
    public function testRaise()
    {
        $this->protectedMethodCall($this->Loader, 'raise', array('LogicException', 'derp'));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     * @covers \josegonzalez\Dotenv\Loader::raise
     */
    public function testRaiseNoException()
    {
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'raise', array('LogicException', 'derp'));
    }

    public function testStatic()
    {
        $dotenv = Loader::load(array(
            'raiseExceptions' => false
        ));
        $this->assertNull($dotenv->toArray());

        $dotenv = Loader::load($this->fixturePath . '.env');
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $dotenv->toArray());

        $dotenv = Loader::load(array(
            'filepath' => $this->fixturePath . '.env',
        ));
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $dotenv->toArray());

        $dotenv = Loader::load(array(
            'filepath' => $this->fixturePath . '.env',
            'prefix' => 'PREFIX_'
        ));
        $this->assertEquals(array(
            'PREFIX_FOO' => 'bar',
            'PREFIX_BAR' => 'baz',
            'PREFIX_SPACED' => 'with spaces',
            'PREFIX_EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $dotenv->toArray());
    }

/**
 * Call a protected method on an object
 *
 * @param Object $object object
 * @param string $name method to call
 * @param array $args arguments to pass to the method
 * @return mixed
 */
    public function protectedMethodCall($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
