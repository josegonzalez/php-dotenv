<?php

namespace josegonzalez\Dotenv;

use josegonzalez\Dotenv\Loader;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Just return what was handed to it.
 * @param mixed $data basically anything
 * @return mixed Whatever was in $data
 */
function doNothing($data)
{
    return $data;
}

class LoaderTest extends TestCase
{
    use PHPMock;

    /** @var array<mixed, mixed> */
    protected $env = [];
    
    /** @var array<mixed, mixed> */
    protected $server = [];

    /** @var string */
    protected $fixturePath = '';

    /** @var \josegonzalez\Dotenv\Loader */
    protected $Loader;

    public function setUp(): void
    {
        $this->env = $_ENV;
        $this->server = $_SERVER;
        $this->fixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $this->Loader = new Loader($this->fixturePath . '.env');
        $GLOBALS['apache_test_data'] = [];
    }

    public function tearDown(): void
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
    public function testFilepath(): void
    {
        $this->assertEquals($this->fixturePath . '.env', $this->Loader->filepath());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::filepaths
     */
    public function testFilepaths(): void
    {
        $this->assertEquals([$this->fixturePath . '.env'], $this->Loader->filepaths());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::setFilepath
     * @covers \josegonzalez\Dotenv\Loader::setFilepaths
     */
    public function testSetFilepath(): void
    {
        $this->Loader->setFilepath('/tmp/.env');
        $this->assertEquals('/tmp/.env', $this->Loader->filepath());

        $this->Loader->setFilepath(null);
        $basePath = realpath(implode(DIRECTORY_SEPARATOR, [
            __DIR__,
            '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..',
            'src' . DIRECTORY_SEPARATOR . 'josegonzalez' . DIRECTORY_SEPARATOR . 'Dotenv',
        ]));
        $this->assertEquals($basePath . DIRECTORY_SEPARATOR .'.env', $this->Loader->filepath());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParse(): void
    {
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
        $this->assertEquals('a value with a # character & a quote " ' .
            'character inside quotes', $environment['CQUOTESWITHQUOTE']);
        $this->assertSame(null, $environment['CNULL']);

        $this->assertEquals([
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
        ], $environment);

        $this->Loader->setFilepath($this->fixturePath . 'cake.env');
        $this->Loader->parse();
        $environment = $this->Loader->toArray();
        $this->assertEquals('app', $environment['APP_NAME']);
        $this->assertSame(2, $environment['DEBUG']);
        $this->assertIsInt($environment['DEBUG']);
        $this->assertEquals('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi', $environment['SECURITY_SALT']);
        $this->assertEquals('76859309657453542496749683645', $environment['SECURITY_CIPHER_SEED']);
        // lines are split to make phpcs happy
        // | WARNING | Line exceeds 120 characters; contains xxx characters
        $this->assertEquals('mysql://user:password@localhost' .
            '/database_name?encoding=utf8', $environment['DATABASE_URL']);
        $this->assertEquals('mysql://user:password@localhost' .
            '/test_database_name?encoding=utf8', $environment['DATABASE_TEST_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/' .
            '?prefix=app_&duration=+2 minutes', $environment['CACHE_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/' .
            '?prefix=app_debug_kit_&duration=+2 minutes', $environment['CACHE_DEBUG_KIT_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/' .
            '?prefix=app_cake_core_&duration=+2 minutes', $environment['CACHE_CAKE_CORE_URL']);
        $this->assertEquals('file:///vagrant/app/tmp/' .
            '?prefix=app_cake_model_&duration=+2 minutes', $environment['CACHE_CAKE_MODEL_URL']);
        $this->assertEquals('file:///vagrant/app/logs/' .
            '?types=notice,info,debug&file=debug', $environment['LOG_URL']);
        $this->assertEquals('file:///vagrant/app/logs/' .
            '?types=warning,error,critical,alert,emergency&file=error', $environment['LOG_ERROR_URL']);
        $this->assertEquals('mail://localhost/?' .
            'from=you@localhost', $environment['EMAIL_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/' .
            '?from[site@localhost]=My+Site&timeout=30', $environment['EMAIL_SMTP_URL']);
        $this->assertEquals('smtp://user:secret@localhost:25/' .
            '?from=you@localhost&messageId=1&template=0&layout=0&timeout=30', $environment['EMAIL_FAST_URL']);
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     * @expectedExceptionMessage
     */
    public function testParseException(): void
    {
        $this->expectException(\M1\Env\Exception\ParseException::class);
        $this->expectExceptionMessage('Key can only contain alphanumeric' .
                ' and underscores and can not start with a number: 01SKIPPED near 01SKIPPED at line 1');
        $this->Loader->setFilepath($this->fixturePath . 'parse_exception.env ');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParseFileNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Environment file '.env' is not found");
        $this->Loader->setFilepath('.env');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParseFileIsDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Environment file '/tmp' is a directory. Should be a file");
        $this->Loader->setFilepath('/tmp');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::parse
     */
    public function testParseFileIsUnreadable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Environment file '/tmp/php-dotenv-unreadable' is not readable");
        touch('/tmp/php-dotenv-unreadable');
        chmod('/tmp/php-dotenv-unreadable', 0000);
        $this->Loader->setFilepath('/tmp/php-dotenv-unreadable');
        $this->Loader->parse();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::filters
     */
    public function testFilters(): void
    {
        $this->assertSame([], $this->Loader->filters());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     */
    public function testSetFilters(): void
    {
        $this->assertSame([], $this->Loader->filters());
        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\NullFilter',
        ]));


        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            'josegonzalez\Dotenv\doNothing',
        ]));

        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            'josegonzalez\Dotenv\doNothing' => ['key' => 'value'],
        ]));

        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            function () {
                return [];
            }
        ]));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     */
    public function testSetFilterNonexistentFilter(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid filter class SomeFilter');
        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            'SomeFilter'
        ]));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::setFilters
     */
    public function testSetFilterInvalidCallable(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid filter class');
        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            $this
        ]));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Filter\NullFilter::__invoke
     */
    public function testFilter(): void
    {
        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\NullFilter',
        ]));
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::filter
     * @covers \josegonzalez\Dotenv\Filter\CallableFilter::__invoke
     */
    public function testFilterCallable(): void
    {
        $this->assertEquals($this->Loader, $this->Loader->setFilters([
            function () {
                return ['FOO' => 'BAR'];
            }
        ]));
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals(['FOO' => 'BAR'], $this->Loader->toArray());
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\LowercaseKeyFilter::__invoke
     */
    public function testLowercaseKeyFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\LowercaseKeyFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'baz',
            'spaced' => 'with spaces',
            'equals' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\NullFilter::__invoke
     */
    public function testNullFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\NullFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }


    /**
     * @covers \josegonzalez\Dotenv\Filter\RemapKeysFilter::__invoke
     */
    public function testRemapKeysFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\RemapKeysFilter' => [
                'FOO' => 'QUX'
            ],
        ]);
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals([
            'QUX' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\UppercaseFirstKeyFilter::__invoke
     */
    public function testUppercaseFirstKeyFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\LowercaseKeyFilter',
            'josegonzalez\Dotenv\Filter\UppercaseFirstKeyFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . '.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $this->assertEquals([
            'Foo' => 'bar',
            'Bar' => 'baz',
            'Spaced' => 'with spaces',
            'Equals' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::__invoke
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::get
     */
    public function testUrlParseFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\UrlParseFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . 'url_parse_filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertSame([
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
        ], $environment);
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\UnderscoreArrayFilter::__invoke
     */
    public function testUnderscoreArrayFilter(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\UnderscoreArrayFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . 'underscore_array_filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertEquals([
            'DATABASE' => [
                'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                0 => [
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'OTHERURL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                ],
                1 => [
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'OTHERURL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                ],
            ],
            'DATA' => [
                'BASE' => [
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8'
                ],
            ],

        ], $environment);
    }

    /**
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::__invoke
     * @covers \josegonzalez\Dotenv\Filter\UrlParseFilter::get
     * @covers \josegonzalez\Dotenv\Filter\UnderscoreArrayFilter::__invoke
     */
    public function testMultipleFilters(): void
    {
        $this->Loader->setFilters([
            'josegonzalez\Dotenv\Filter\UrlParseFilter',
            'josegonzalez\Dotenv\Filter\UnderscoreArrayFilter',
        ]);
        $this->Loader->setFilepath($this->fixturePath . 'filter.env');
        $this->Loader->parse();
        $this->Loader->filter();
        $environment = $this->Loader->toArray();
        $this->assertSame([
            'DATABASE' => [
                'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                'SCHEME' => 'mysql',
                'HOST' => 'localhost',
                'PORT' => '',
                'USER' => 'user',
                'PASS' => 'password',
                'PATH' => '/database_name',
                'QUERY' => 'encoding=utf8',
                'FRAGMENT' => '',
            ],
            'DATA' => [
                'BASE' => [
                    'URL' => 'mysql://user:password@localhost/database_name?encoding=utf8',
                    'SCHEME' => 'mysql',
                    'HOST' => 'localhost',
                    'PORT' => '',
                    'USER' => 'user',
                    'PASS' => 'password',
                    'PATH' => '/database_name',
                    'QUERY' => 'encoding=utf8',
                    'FRAGMENT' => '',
                ],
            ],
        ], $environment);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     */
    public function testExpect(): void
    {
        $this->Loader->parse();
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect('FOO'));
        $this->assertInstanceOf('josegonzalez\Dotenv\Loader', $this->Loader->expect(['FOO', 'BAR']));
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect With no parsing done yet.
     */
    public function testExpectRequireParse(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Environment must be parsed before calling expect()');
        $this->Loader->expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     */
    public function testExpectLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No arguments were passed to expect()');
        $this->Loader->parse();
        $this->Loader->expect();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::expect
     */
    public function testExpectRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Required ENV vars missing: ['INVALID']");
        $this->Loader->parse();
        $this->Loader->expect('INVALID');
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenvExceptionUnavailable(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to undefined function josegonzalez\Dotenv\apache_getenv()');
        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenv(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
        }

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
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::apacheSetenv
     */
    public function testToApacheSetenvSkip(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
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
     */
    public function testToApacheSetenvException(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
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

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Key "FOO" has already been defined in apache_getenv()');
        $this->Loader->parse();
        $this->Loader->apacheSetenv(false);
        $this->Loader->apacheSetenv(false);
    }


    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToApacheSetenvPreserveZeros(): void
    {
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $this->markTestSkipped('Unable to mock bare php functions');
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
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::define
     */
    public function testDefine(): void
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
    public function testDefineSkip(): void
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
     */
    public function testDefineException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Key "FOO" has already been defined');
        $this->Loader->parse();
        $this->Loader->define();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenv(): void
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
    public function testToPutenvSkip(): void
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
     */
    public function testToPutenvException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Key "FOO" has already been defined in getenv()');
        $this->Loader->parse();
        $this->Loader->putenv(false);
        $this->Loader->putenv(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::putenv
     */
    public function testToPutenvPreserveZeros(): void
    {
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
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnv(): void
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
    public function testToEnvSkip(): void
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
     */
    public function testToEnvException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Key "FOO" has already been defined in $_ENV');
        $this->Loader->parse();
        $this->Loader->toEnv(false);
        $this->Loader->toEnv(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toEnv
     */
    public function testToEnvPreserveZeros(): void
    {
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
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServer(): void
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
    public function testToServerSkip(): void
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
     */
    public function testToServerException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Key "FOO" has already been defined in $_SERVER');
        $this->Loader->parse();
        $this->Loader->toServer(false);
        $this->Loader->toServer(false);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toServer
     */
    public function testToServerPreserveZeros(): void
    {
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
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::skipped
     * @covers \josegonzalez\Dotenv\Loader::skipExisting
     */
    public function testSkipExisting(): void
    {
        $this->assertEquals([], $this->Loader->skipped());

        $this->Loader->skipExisting('toEnv');
        $this->assertEquals(['toEnv'], $this->Loader->skipped());

        $this->Loader->skipExisting(['toEnv']);
        $this->assertEquals(['toEnv'], $this->Loader->skipped());

        $this->Loader->skipExisting();
        $this->assertEquals(['apacheSetenv', 'define', 'putenv', 'toEnv', 'toServer'], $this->Loader->skipped());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::prefix
     * @covers \josegonzalez\Dotenv\Loader::prefixed
     */
    public function testPrefix(): void
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
    public function testToArray(): void
    {
        $this->Loader->parse();
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $this->Loader->toArray());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::toArray With no parsing done yet.
     */
    public function testToArrayRequireParse(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Environment must be parsed before calling toArray()');
        $this->Loader->toArray();
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::__toString
     */
    public function testToString(): void
    {
        $this->assertEquals('[]', $this->Loader->__toString());

        $this->Loader->parse();
        // phpcs error: | WARNING | Line exceeds 120 characters; contains 153 characters
        $this->assertEquals('{"FOO":"bar","BAR":"baz","SPACED":"with spaces",' .
            '"EQUALS":"pgsql:host=localhost;dbname=test"}', $this->Loader->__toString());
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::requireParse
     */
    public function testRequireParse(): void
    {
        $this->expectNotToPerformAssertions();
        $this->Loader->parse();
        $this->protectedMethodCall($this->Loader, 'requireParse', ['toEnv']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::requireParse With no parsing done yet.
     */
    public function testRequireParseException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Environment must be parsed before calling toEnv()');
        $this->protectedMethodCall($this->Loader, 'requireParse', ['toEnv']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     */
    public function testRequireParseNoException(): void
    {
        $this->expectNotToPerformAssertions();
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'requireParse', ['toEnv']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::raise
     */
    public function testRaise(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('derp');
        $this->protectedMethodCall($this->Loader, 'raise', ['LogicException', 'derp']);
    }

    /**
     * @covers \josegonzalez\Dotenv\Loader::raiseExceptions
     * @covers \josegonzalez\Dotenv\Loader::raise
     */
    public function testRaiseNoException(): void
    {
        $this->expectNotToPerformAssertions();
        $this->Loader->raiseExceptions(false);
        $this->protectedMethodCall($this->Loader, 'raise', ['LogicException', 'derp']);
    }

    public function testStatic(): void
    {
        $dotenv = Loader::load([
            'raiseExceptions' => false
        ]);
        $this->assertNull($dotenv->toArray());

        $dotenv = Loader::load($this->fixturePath . '.env');
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $dotenv->toArray());

        $dotenv = Loader::load([
            'filepath' => $this->fixturePath . '.env',
        ]);
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $dotenv->toArray());

        $dotenv = Loader::load([
            'filepaths' => [$this->fixturePath . '.env'],
        ]);
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $dotenv->toArray());

        $dotenv = Loader::load([
            'filepaths' => [
                $this->fixturePath . '.env.nonexistent',
                $this->fixturePath . '.env',
            ],
        ]);
        $this->assertEquals([
            'FOO' => 'bar',
            'BAR' => 'baz',
            'SPACED' => 'with spaces',
            'EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $dotenv->toArray());

        $dotenv = Loader::load([
            'filepath' => $this->fixturePath . '.env',
            'prefix' => 'PREFIX_'
        ]);
        $this->assertEquals([
            'PREFIX_FOO' => 'bar',
            'PREFIX_BAR' => 'baz',
            'PREFIX_SPACED' => 'with spaces',
            'PREFIX_EQUALS' => 'pgsql:host=localhost;dbname=test',
        ], $dotenv->toArray());

        $dotenv = Loader::load([
            'filepath' => $this->fixturePath . 'url_parse_filter.env',
            'filters' => ['josegonzalez\Dotenv\Filter\UrlParseFilter'],
        ]);
        $this->assertSame([
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
        ], $dotenv->toArray());
    }

/**
 * Call a protected method on an object
 *
 * @param object $obj object
 * @param string $name method to call
 * @param array<mixed, mixed> $args arguments to pass to the method
 * @return mixed
 */
    public function protectedMethodCall(object $obj, string $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
