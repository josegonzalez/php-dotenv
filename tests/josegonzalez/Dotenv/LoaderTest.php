<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Loader;
use phpmock\phpunit\PHPMock;
use \PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

function doNothing($data)
{
    return $data;
}

class LoaderTest extends PHPUnit_Framework_TestCase
{
    use PHPMock;
    protected $env = array();

    protected $server = array();

    protected $fixturePath = '';

    protected $Loader;

    /**
     * Hopefully this will allow php > 7.1 to run.
     * Phpunit >= 8.0 uses setUp(): void which this needs to match, but will break php 5.x
     */
    public function compatibleSetUp()
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
        $this->fixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $this->Loader = new Loader($this->fixturePath . '.env');
        $GLOBALS['apache_test_data'] = array();
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
        unset($this->fixturePath);
        unset($this->Loader);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::filepath
     */
    public function testFilepath()
    {
        $this->compatibleSetUp();
        $this->assertEquals($this->fixturePath . '.env', $this->Loader->filepath());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     */
    public function testFilepaths()
    {
        $this->compatibleSetUp();
        $this->assertEquals(array($this->fixturePath . '.env'), $this->Loader->filepaths());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::filepath
     */
    public function testSetFilepath()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilepath('/tmp/.env');
        $this->assertEquals('/tmp/.env', $this->Loader->filepath());

        $this->Loader->setFilepath(null);
        $basePath = realpath(implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..',
            'src' . DIRECTORY_SEPARATOR . 'josegonzalez' . DIRECTORY_SEPARATOR . 'Dotenv',
        )));
        $this->assertEquals($basePath . DIRECTORY_SEPARATOR .'.env', $this->Loader->filepath());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParse()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilepath($this->fixturePath . 'all.env');
        $this->Loader->parse();
        $environment = $this->Loader->toArray();
        $this->assertEquals('bar', $environment['FOO']);
        $this->assertEquals('baz', $environment['BAR']);
        $this->assertEquals('unquotedwithspaces spaces', $environment['SPACED']);
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
        $this->assertSame(null, $environment['CNULL']);

        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'unquotedwithspaces spaces',
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
            'CNULL' => null,
            'SPVAR1' => '$a6^C7k%zs+e^.jvjXk',
            'SPVAR2' => '?BUty3koaV3%GA*hMAwH}B',
            'SPVAR3' => 'jdgEB4{QgEC]HL))&GcXxokB+wqoN+j>xkV7K?m$r',
            'SPVAR4' => '22222:22#2^{',
            'SPVAR5' => 'test some escaped characters like a quote " or maybe a backslash \\\\\\\\',
            'NVAR1' => 'Hello',
            'NVAR2' => 'World!',
            'NVAR3' => 'Hello World!',
            'NVAR4' => '{$NVAR1} {$NVAR2}',
            'NVAR5' => '$NVAR1 {NVAR2}',
            'NULLVAR1' => null,
            'NVAR6' => ' Hello',
            'PHP_NULL' => null,
            'STRING_NULL' => 'null',
            'PHP_TRUE' => true,
            'STRING_TRUE' => 'true',
            'PHP_FALSE' => false,
            'STRING_FALSE' => 'false',
            'STRING_EMPTY' => '',
            'STRING_EMPTY_2' => '',
            'NO_VALUE_INLINE_COMMENT' => null,
        ), $environment);

        $this->Loader->setFilepath($this->fixturePath . 'cake.env');
        $this->Loader->parse();
        $environment = $this->Loader->toArray();
        $this->assertEquals('app', $environment['APP_NAME']);
        if (method_exists($this, 'assertInternalType')) {
            $this->assertInternalType('int', $environment['DEBUG']);
        }
        $this->assertSame(2, $environment['DEBUG']); // this also tests the typecast, in this case, an int.
        $this->assertEquals('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi', $environment['SECURITY_SALT']);
        $this->assertEquals('76859309657453542496749683645', $environment['SECURITY_CIPHER_SEED']);
        $this->assertEquals('mysql://user:password@localhost/database_name?encoding=utf8', $environment['DATABASE_URL']);
        $this->assertEquals('mysql://user:password@localhost/test_database_name?encoding=utf8', $environment['DATABASE_TEST_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/?prefix=app_&duration=+2 minutes', $environment['CACHE_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/?prefix=app_debug_kit_&duration=+2 minutes', $environment['CACHE_DEBUG_KIT_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/?prefix=app_cake_core_&duration=+2 minutes', $environment['CACHE_CAKE_CORE_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/?prefix=app_cake_model_&duration=+2 minutes', $environment['CACHE_CAKE_MODEL_URL']);
        $this->assertEquals('file:///vagrant/app/logs/?types=notice,info,debug&file=debug', $environment['LOG_URL']);
        $this->assertEquals('file:///vagrant/app/logs/?types=warning,error,critical,alert,emergency&file=error', $environment['LOG_ERROR_URL']);
        $this->assertEquals('mail://localhost/?from=you@localhost', $environment['EMAIL_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/?from[site@localhost]=My+Site&timeout=30', $environment['EMAIL_SMTP_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/?from=you@localhost&messageId=1&template=0&layout=0&timeout=30', $environment['EMAIL_FAST_URL']);
        $this->compatibleTearDown();
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException M1\Env\Exception\ParseException
     * @expectedExceptionMessage Key can only contain alphanumeric and underscores and can not start with a number: 01SKIPPED near 01SKIPPED at line 1
     */
    public function testParseException()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\M1\Env\Exception\ParseException::class);
            $this->expectExceptionMessage('Key can only contain alphanumeric' .
                ' and underscores and can not start with a number: 01SKIPPED near 01SKIPPED at line 1');
        }
        $this->Loader->setFilepath($this->fixturePath . 'parse_exception.env');
        $this->Loader->parse();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Environment file '.env' is not found
     */
    public function testParseFileNotFound()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Environment file '.env' is not found");
        }
        $this->Loader->setFilepath('.env');
        $this->Loader->parse();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Environment file '/tmp' is a directory. Should be a file
     */
    public function testParseFileIsDirectory()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Environment file '/tmp' is a directory. Should be a file");
        }
        $this->Loader->setFilepath('/tmp');
        $this->Loader->parse();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Environment file '/tmp/php-dotenv-unreadable' is not readable
     */
    public function testParseFileIsUnreadable()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Environment file '/tmp/php-dotenv-unreadable' is not readable");
        }
        touch('/tmp/php-dotenv-unreadable');
        chmod('/tmp/php-dotenv-unreadable', 0000);
        $this->Loader->setFilepath('/tmp/php-dotenv-unreadable');
        $this->Loader->parse();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::filters
     */
    public function testFilters()
    {
        $this->compatibleSetUp();
        $this->assertSame(array(), $this->Loader->filters());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filters
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     */
    public function testSetFilters()
    {
        $this->compatibleSetUp();
        $this->assertSame(array(), $this->Loader->filters());
        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\NullFilter',
        )));


        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\doNothing',
        )));

        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\doNothing' => array('key' => 'value'),
        )));

        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            function () {
                return array();
            }
        )));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid filter class SomeFilter
     */
    public function testSetFilterNonexistentFilter()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Invalid filter class SomeFilter');
        }
        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            'SomeFilter'
        )));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @expectedException LogicException
     * @expectedExceptionMessage Invalid filter class
     */
    public function testSetFilterInvalidCallable()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Invalid filter class');
        }
        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            $this
        )));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Filter\NullFilter::__invoke
     */
    public function testFilter()
    {
        $this->compatibleSetUp();
        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\NullFilter',
        )));
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Filter\CallableFilter::__invoke
     */
    public function testFilterCallable()
    {
        $this->compatibleSetUp();
        $this->assertEquals($this->Loader, $this->Loader->setFilters(array(
            function () {
                return array('FOO' => 'BAR');
            }
        )));
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array('FOO' => 'BAR'), $this->Loader->toArray());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\LowercaseKeyFilter::__invoke
     */
    public function testLowercaseKeyFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\LowercaseKeyFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array(
            'foo' => 'bar',
            'bar' => 'baz',
            'spaced' => 'with spaces',
            'equals' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\NullFilter::__invoke
     */
    public function testNullFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\NullFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\RemapKeysFilter::__invoke
     */
    public function testRemapKeysFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\RemapKeysFilter' => array(
                'FOO' => 'QUX'
            ),
        ));
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array(
            'QUX' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\LowercaseKeyFilter::__invoke
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\UppercaseFirstKeyFilter::__invoke
     */
    public function testUppercaseFirstKeyFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\LowercaseKeyFilter',
            'josegonzalez\Dotenv\Filter\UppercaseFirstKeyFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(array(
            'Foo' => 'bar',
            'Bar' => 'baz',
            'Spaced' => 'with spaces',
            'Equals' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::__invoke
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::get
     */
    public function testUrlParseFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\UrlParseFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . 'url_parse_filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertSame(array(
            'READ_DATABASE_URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
            'READ_DATABASE_SCHEME' => 'mysql',
            'READ_DATABASE_HOST' => 'localhost',
            'READ_DATABASE_PORT' => '',
            'READ_DATABASE_USER' => 'user',
            'READ_DATABASE_PASS' => 'password',
            'READ_DATABASE_PATH' => '/database_name',
            'READ_DATABASE_QUERY' => 'encoding=utf8',
            'READ_DATABASE_FRAGMENT' => '',
            'DATABASE_URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
            'DATABASE_SCHEME' => 'mysql',
            'DATABASE_HOST' => 'localhost',
            'DATABASE_PORT' => '',
            'DATABASE_USER' => 'user',
            'DATABASE_PASS' => 'password',
            'DATABASE_PATH' => '/database_name',
            'DATABASE_QUERY' => 'encoding=utf8',
            'DATABASE_FRAGMENT' => '',
        ), $environment);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\UnderscoreArrayFilter::__invoke
     */
    public function testUnderscoreArrayFilter()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\UnderscoreArrayFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . 'underscore_array_filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertEquals(array(
            'DATABASE' => array(
                'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                0 => array(
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'OTHERURL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                ),
                1 => array(
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'OTHERURL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                ),
            ),
            'DATA' => array(
                'BASE' => array(
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8'
                ),
            ),

        ), $environment);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::__invoke
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::get
     * @covers \josegonzalez\Dotenv\Filter\UnderscoreArrayFilter::__invoke
     */
    public function testMultipleFilters()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilters(array(
            'josegonzalez\Dotenv\Filter\UrlParseFilter',
            'josegonzalez\Dotenv\Filter\UnderscoreArrayFilter',
        ));
        $this->Loader->setFilepath($this->fixturePath . 'filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertSame(array(
            'DATABASE' => array(
                'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                'SCHEME' => 'mysql',
                'HOST' => 'localhost',
                'PORT' => '',
                'USER' => 'user',
                'PASS' => 'password',
                'PATH' => '/database_name',
                'QUERY' => 'encoding=utf8',
                'FRAGMENT' => '',
            ),
            'DATA' => array(
                'BASE' => array(
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'SCHEME' => 'mysql',
                    'HOST' => 'localhost',
                    'PORT' => '',
                    'USER' => 'user',
                    'PASS' => 'password',
                    'PATH' => '/database_name',
                    'QUERY' => 'encoding=utf8',
                    'FRAGMENT' => '',
                ),
            ),
        ), $environment);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::expect
     */
    public function testExpect()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect('FOO'));
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect(array('FOO', 'BAR')));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::expect
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling expect()
     */
    public function testExpectRequireParse()
    {
        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Environment must be parsed before calling expect()');
        }
        $this->Loader->expect();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::expect
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
        $this->Loader->parse();
        $this->Loader->expect();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Expect::__construct
     * @covers \josegonzalez\Dotenv\Expect::__invoke
     * @covers \josegonzalez\Dotenv\Expect::raise
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::expect
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
        $this->Loader->parse();
        $this->Loader->expect('INVALID');
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenvExceptionUnavailable()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->compatibleSetUp();
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to undefined function josegonzalez\Dotenv\apache_getenv()');
        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenv()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->compatibleSetUp();
        $apacheGetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_getenv');
        $apacheGetenv->expects($this->any())->willReturnCallback(
            function ($key) {
                if (isset($GLOBALS['apache_test_data'][$key])) {
                    return $GLOBALS['apache_test_data'][$key];
                }
                return false;
            }
        );
        $apacheSetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_setenv');
        $apacheSetenv->expects($this->any())->willReturnCallback(
            function ($key, $value) {
                $GLOBALS['apache_test_data'][$key] = (string)$value;
                return true;
            }
        );

        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);

        $this->assertEquals('bar', apache_getenv('FOO'));
        $this->assertEquals('baz', apache_getenv('BAR'));
        $this->assertEquals('with spaces', apache_getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', apache_getenv('EQUALS'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenvSkip()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->compatibleSetUp();
        $apacheGetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_getenv');
        $apacheGetenv->expects($this->any())->willReturnCallback(
            function ($key) {
                if (isset($GLOBALS['apache_test_data'][$key])) {
                    return (string)$GLOBALS['apache_test_data'][$key];
                }
                return false;
            }
        );
        $apacheSetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_setenv');
        $apacheSetenv->expects($this->any())->willReturnCallback(
            function ($key, $value) {
                $GLOBALS['apache_test_data'][$key] = (string)$value;
                return true;
            }
        );

        $this->Loader->parse();
        $this->Loader->skipExisting('apacheSetenv');
        $this->Loader->apacheSetenv(false);
        $this->Loader->apacheSetenv(false);

        $this->assertEquals('bar', apache_getenv('FOO'));
        $this->assertEquals('baz', apache_getenv('BAR'));
        $this->assertEquals('with spaces', apache_getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', apache_getenv('EQUALS'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in apache_getenv()
     */
    public function testToApacheSetenvException()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->compatibleSetUp();
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Key "FOO" has already been defined in apache_getenv()');
        }

        $apacheGetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_getenv');
        $apacheGetenv->expects($this->any())->willReturnCallback(
            function ($key) {
                if (isset($GLOBALS['apache_test_data'][$key])) {
                    return (string)$GLOBALS['apache_test_data'][$key];
                }
                return false;
            }
        );
        $apacheSetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_setenv');
        $apacheSetenv->expects($this->any())->willReturnCallback(
            function ($key, $value) {
                $GLOBALS['apache_test_data'][$key] = (string)$value;
                return true;
            }
        );

        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
        $this->Loader->apacheSetenv(false);
        $this->compatibleTearDown();
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToApacheSetenvPreserveZeros()
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->compatibleSetUp();
        $apacheGetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_getenv');
        $apacheGetenv->expects($this->any())->willReturnCallback(
            function ($key) {
                if (isset($GLOBALS['apache_test_data'][$key])) {
                    return (string)$GLOBALS['apache_test_data'][$key];
                }
                return false;
            }
        );
        $apacheSetenv = $this->getFunctionMock(__NAMESPACE__, 'apache_setenv');
        $apacheSetenv->expects($this->any())->willReturnCallback(
            function ($key, $value) {
                $GLOBALS['apache_test_data'][$key] = (string)$value;
                return true;
            }
        );

        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_0.env');
        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);

        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_1.env');
        $this->Loader->parse();
        $this->Loader->skipExisting('apacheSetenv');
        $this->Loader->apacheSetenv(false);

        $this->assertEquals('0', apache_getenv('Z_NUMBER'));
        $this->assertEquals('', apache_getenv('Z_BOOL'));
        $this->assertEquals('', apache_getenv('Z_STRING'));
        $this->assertEquals('', apache_getenv('Z_NULLABLE'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::define
     */
    public function testDefine()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->define();

        $this->assertEquals('bar', FOO);
        $this->assertEquals('baz', BAR);
        $this->assertEquals('with spaces', SPACED);
        $this->assertEquals('pgsql:host=localhost;dbname=test', EQUALS);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::define
     */
    public function testDefineSkip()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->skipExisting('define');
        $this->Loader->define();

        $this->assertEquals('bar', FOO);
        $this->assertEquals('baz', BAR);
        $this->assertEquals('with spaces', SPACED);
        $this->assertEquals('pgsql:host=localhost;dbname=test', EQUALS);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::define
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined
     */
    public function testDefineException()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Key "FOO" has already been defined');
        }
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->define();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenv()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->putenv(false);

        $this->assertEquals('bar', getenv('FOO'));
        $this->assertEquals('baz', getenv('BAR'));
        $this->assertEquals('with spaces', getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', getenv('EQUALS'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenvSkip()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->skipExisting('putenv');
        $this->Loader->putenv(false);
        $this->Loader->putenv(false);

        $this->assertEquals('bar', getenv('FOO'));
        $this->assertEquals('baz', getenv('BAR'));
        $this->assertEquals('with spaces', getenv('SPACED'));
        $this->assertEquals('pgsql:host=localhost;dbname=test', getenv('EQUALS'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::putenv
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in getenv()
     */
    public function testToPutenvException()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Key "FOO" has already been defined in getenv()');
        }
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->putenv(false);
        $this->Loader->putenv(false);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenvPreserveZeros()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_0.env');
        $this->Loader->parse();
        $this->Loader->putenv(false);

        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_1.env');
        $this->Loader->parse();
        $this->Loader->skipExisting('putenv');
        $this->Loader->putenv(false);

        $this->assertEquals('0', getenv('Z_NUMBER'));
        $this->assertEquals('', getenv('Z_BOOL'));
        $this->assertEquals('', getenv('Z_STRING'));
        $this->assertEquals('', getenv('Z_NULLABLE'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnv()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->toEnv(false);

        $this->assertEquals('bar', $_ENV['FOO']);
        $this->assertEquals('baz', $_ENV['BAR']);
        $this->assertEquals('with spaces', $_ENV['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['EQUALS']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnvSkip()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->skipExisting('toEnv');
        $this->Loader->toEnv(false);
        $this->Loader->toEnv(false);

        $this->assertEquals('bar', $_ENV['FOO']);
        $this->assertEquals('baz', $_ENV['BAR']);
        $this->assertEquals('with spaces', $_ENV['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_ENV['EQUALS']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in $_ENV
     */
    public function testToEnvException()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Key "FOO" has already been defined in $_ENV');
        }
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->toEnv(false);
        $this->Loader->toEnv(false);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnvPreserveZeros()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_0.env');
        $this->Loader->parse();
        $this->Loader->toEnv(false);

        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_1.env');
        $this->Loader->parse();
        $this->Loader->skipExisting('toEnv');
        $this->Loader->toEnv(false);

        $this->assertEquals(0, $_ENV['Z_NUMBER']);
        $this->assertEquals(false, $_ENV['Z_BOOL']);
        $this->assertEquals('', $_ENV['Z_STRING']);
        $this->assertEquals(null, $_ENV['Z_NULLABLE']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServer()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->toServer(false);

        $this->assertEquals('bar', $_SERVER['FOO']);
        $this->assertEquals('baz', $_SERVER['BAR']);
        $this->assertEquals('with spaces', $_SERVER['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_SERVER['EQUALS']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServerSkip()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->skipExisting('toServer');
        $this->Loader->toServer(false);
        $this->Loader->toServer(false);

        $this->assertEquals('bar', $_SERVER['FOO']);
        $this->assertEquals('baz', $_SERVER['BAR']);
        $this->assertEquals('with spaces', $_SERVER['SPACED']);
        $this->assertEquals('pgsql:host=localhost;dbname=test', $_SERVER['EQUALS']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toServer
     * @expectedException LogicException
     * @expectedExceptionMessage Key "FOO" has already been defined in $_SERVER
     */
    public function testToServerException()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Key "FOO" has already been defined in $_SERVER');
        }
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->Loader->toServer(false);
        $this->Loader->toServer(false);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServerPreserveZeros()
    {
        $this->compatibleSetUp();
        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_0.env');
        $this->Loader->parse();
        $this->Loader->toServer(false);

        $this->Loader->setFilepaths($this->fixturePath . 'zero_test_1.env');
        $this->Loader->parse();
        $this->Loader->skipExisting('toServer');
        $this->Loader->toServer(false);

        $this->assertEquals(0, $_SERVER['Z_NUMBER']);
        $this->assertEquals(false, $_SERVER['Z_BOOL']);
        $this->assertEquals('', $_SERVER['Z_STRING']);
        $this->assertEquals(null, $_SERVER['Z_NULLABLE']);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::skipped
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     */
    public function testSkipExisting()
    {
        $this->compatibleSetUp();
        $this->assertEquals(array(), $this->Loader->skipped());

        $this->Loader->skipExisting('toEnv');
        $this->assertEquals(array('toEnv'), $this->Loader->skipped());

        $this->Loader->skipExisting(array('toEnv'));
        $this->assertEquals(array('toEnv'), $this->Loader->skipped());

        $this->Loader->skipExisting();
        $this->assertEquals(array('apacheSetenv', 'define', 'putenv', 'toEnv', 'toServer'), $this->Loader->skipped());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::prefix
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     */
    public function testPrefix()
    {
        $this->compatibleSetUp();
        $this->assertEquals('KEY', $this->Loader->prefixed('KEY'));

        $this->Loader->prefix('PREFIX_');
        $this->assertEquals('PREFIX_KEY', $this->Loader->prefixed('KEY'));

        $this->Loader->prefix('PREFIX_TWO_');
        $this->assertEquals('PREFIX_TWO_KEY', $this->Loader->prefixed('KEY'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toArray
     */
    public function testToArray()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $this->Loader->toArray());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling toArray()
     */
    public function testToArrayRequireParse()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Environment must be parsed before calling toArray()');
        }
        $this->compatibleSetUp();
        $this->Loader->toArray();
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::toArray
     * @covers \josegonzalez\Dotenv\Loader::__toString
     */
    public function testToString()
    {
        $this->compatibleSetUp();
        $this->assertEquals('[]', $this->Loader->__toString());

        $this->Loader->parse();
        $this->assertEquals('{"FOO":"bar","BAR":"baz","SPACED":"with spaces","EQUALS":"pgsql:host=localhost;dbname=test"}', $this->Loader->__toString());
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     */
    public function testRequireParse()
    {
        $this->compatibleSetUp();
        $this->Loader->parse();
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
        $this->assertSame(true, true);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @expectedException LogicException
     * @expectedExceptionMessage Environment must be parsed before calling toEnv()
     */
    public function testRequireParseException()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('Environment must be parsed before calling toEnv()');
        }
        $this->compatibleSetUp();
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     */
    public function testRequireParseNoException()
    {
        $this->compatibleSetUp();
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'requireParse', array('toEnv'));
        $this->assertSame(true, true);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @expectedException LogicException
     * @expectedExceptionMessage derp
     */
    public function testRaise()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage('derp');
        }
        $this->compatibleSetUp();
        $this->protectedMethodCall($this->Loader, 'raise', array('LogicException', 'derp'));
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     * @covers \josegonzalez\Dotenv\Loader::raise
     */
    public function testRaiseNoException()
    {
        $this->compatibleSetUp();
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'raise', array('LogicException', 'derp'));
        $this->assertSame(true, true);
        $this->compatibleTearDown();
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::__invoke
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::get
     * @covers \josegonzalez\Dotenv\Loader::__construct
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @covers \josegonzalez\Dotenv\Loader::prefix
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     * @covers \josegonzalez\Dotenv\Loader::raise
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     * @covers \josegonzalez\Dotenv\Loader::load
     * @covers \josegonzalez\Dotenv\Loader::toArray
     */
    public function testStatic()
    {
        $this->compatibleSetUp();
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
            'filepaths' => array($this->fixturePath . '.env'),
        ));
        $this->assertEquals(array(
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ), $dotenv->toArray());

        $dotenv = Loader::load(array(
            'filepaths' => array(
                $this->fixturePath . '.env.nonexistent',
                $this->fixturePath . '.env',
            ),
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

        $dotenv = Loader::load(array(
            'filepath' => $this->fixturePath . 'url_parse_filter.env',
            'filters' => array('josegonzalez\Dotenv\Filter\UrlParseFilter'),
        ));
        $this->assertSame(array(
            'READ_DATABASE_URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
            'READ_DATABASE_SCHEME' => 'mysql',
            'READ_DATABASE_HOST' => 'localhost',
            'READ_DATABASE_PORT' => '',
            'READ_DATABASE_USER' => 'user',
            'READ_DATABASE_PASS' => 'password',
            'READ_DATABASE_PATH' => '/database_name',
            'READ_DATABASE_QUERY' => 'encoding=utf8',
            'READ_DATABASE_FRAGMENT' => '',
            'DATABASE_URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
            'DATABASE_SCHEME' => 'mysql',
            'DATABASE_HOST' => 'localhost',
            'DATABASE_PORT' => '',
            'DATABASE_USER' => 'user',
            'DATABASE_PASS' => 'password',
            'DATABASE_PATH' => '/database_name',
            'DATABASE_QUERY' => 'encoding=utf8',
            'DATABASE_FRAGMENT' => '',
        ), $dotenv->toArray());
        $this->compatibleTearDown();
    }

/**
 * Call a protected method on an object
 *
 * @param object $obj object
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
