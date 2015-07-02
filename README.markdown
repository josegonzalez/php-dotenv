[![Build Status](https://travis-ci.org/josegonzalez/php-dotenv.png?branch=master)](https://travis-ci.org/josegonzalez/php-dotenv) [![Coverage Status](https://coveralls.io/repos/josegonzalez/php-dotenv/badge.png?branch=master)](https://coveralls.io/r/josegonzalez/php-dotenv?branch=master) [![Total Downloads](https://poser.pugx.org/josegonzalez/dotenv/d/total.png)](https://packagist.org/packages/josegonzalez/dotenv) [![Latest Stable Version](https://poser.pugx.org/josegonzalez/dotenv/v/stable.png)](https://packagist.org/packages/josegonzalez/dotenv)

# PHP Dotenv
`.env` file parsing for PHP

## What is it and why should I use it?
When developing and deploying your applications you are interacting with two different environments. These two places both execute your code but will do so using different credentials, such as database connection credentials, for example. How do you tackle these differing credentials? dotEnv will solve this issue by allowing you to configure your environments and easily switch between them.

## Requirements

* PHP 5.3+

## Installation

_[Using [Composer](http://getcomposer.org/)]_

Run `composer require josegonzalez/dotenv:dev-master`

Or add the plugin to your project's `composer.json` - something like this:

```composer
  {
    "require": {
      "josegonzalez/dotenv": "dev-master"
    }
  }
```

## Usage

Create a new loader:

```php
<?php
$Loader = new josegonzalez\Dotenv\Loader('path/to/.env');
// Parse the .env file
$Loader->parse();
// Send the parsed .env file to the $_ENV variable
$Loader->toEnv();
?>
```

Most methods return the loader directly, so the following is also possible:

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->toEnv(); // Throws LogicException if ->parse() is not called first
?>
```

You can use a `.env` file with any of the following features:

```shell
# comments are allowed
FOO=bar # you can also have comments on the end of a line
export BAR=baz # you can optionally begin with an `export` statement

# both single and double quotes are allowed
BAZ='qux'
QUX="quux"

# as are escaped quotes or similar:
QUUX="corge \" grault"
CORGE='garply" waldo'

# unquoted values containing [null, true, false] are turned into
# their PHP equivalents
PHP_NULL=null
PHP_TRUE=true
PHP_FALSE=false

# when quoted, they are simply string values
STRING_NULL="null"
STRING_TRUE="true"
STRING_FALSE="false"

# spaces are allowed as well
# in a slightly more relaxed form from bash
 GRAULT =fred
GARPLY = plugh
SPACES=" quote values with spaces" # will contain preceding space

# When using newlines, you should use quoted values
QUOTED_NEWLINE="newline\\nchar"

# you can even have nested variables using `${VAR}` syntax
# remember to define the nested var *before* using it
WALDO=${xyzzy} # not yet defined, so will result in WALDO = `{}`
THUD=${GARPLY} # will be defined as `plugh`

# note that variables beginning with a character
# other than [a-zA-Z_] shall be skipped.
# However, numbers *are* allowed elsewhere in the key
01SKIPPED=skipped
NOT_SKIPPED1=not skipped # will have the value `not`
```

> Example `.env` files are available in the [fixtures](https://github.com/josegonzalez/php-dotenv/tree/master/tests/josegonzalez/fixtures) directory.

### Defining Constants

You can also define constants automatically from your env file:

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->define(); // Throws LogicException if ->parse() is not called first
?>
```

Already defined constants will result in an immediate `LogicException`.

### Adding to `$_ENV`

```php
<?php
$overwriteENV = true;
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->toEnv($overwriteENV); // Throws LogicException if ->parse() is not called first
?>
```

Already defined `$_ENV` entries will result in an immediate `LogicException`, unless `$overwriteENV` is set to `true` (default `false`).

### Adding to `$_SERVER`

```php
<?php
$overwriteSERVER = true;
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->toServer($overwriteSERVER); // Throws LogicException if ->parse() is not called first
?>
```

Already defined `$_SERVER` entries will result in an immediate `LogicException`, unless `$overwriteSERVER` is set to `true` (default `false`).

### Making available to `getenv()`

```php
<?php
$overwrite = true;
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->putenv($overwrite); // Throws LogicException if ->parse() is not called first
?>
```

Already defined `getenv()` entries will result in an immediate `LogicException`, unless `$overwriteSERVER` is set to `true` (default `false`).

### Setting key prefixes

```php
<?php
$environment = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->prefix('FOO')
              ->toServer(); // BAR=baz becomes FOOBAR=baz
?>
```

### Return as array

```php
<?php
$environment = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->toArray(); // Throws LogicException if ->parse() is not called first
?>
```

### Return as json

```php
<?php
$jsonEnvironment = (string)((new josegonzalez\Dotenv\Loader('path/to/.env'))->parse());
?>
```

### Require environment variables

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->parse()
              ->expect('FOO', 'BAR', 'BAZ'); // Throws RuntimeException if variables are missing
?>
```

### Turning off exceptions

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->raiseExceptions(false)
              ->parse()
              ->expect('FOO', 'BAR', 'BAZ'); // Returns false if variables are missing
?>
```

### Filtering environments

It is possible to optionally filter the environment data produced by php-dotenv through the use of filter classes. A filter class has an `__invoke` method like so:

```php
<?php
class LollipopFilter
{
    public function __invoke(array $environment)
    {
        $newEnvironment = [];
        foreach ($environment as $key => $value) {
            $newEnvironment[$key] = 'lollipop';
        }
        return $newEnvironment;
    }
}
```

You can attach filters using the `setFilters()` method, which will override all currently specified filters. If an invalid filter is specified, a LogicException will be thrown.

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->setFilters(['LollipopFilter']); // Takes an array of namespaced class names
?>
```

Finally, to invoke a filter, you must call `filter()` after calling `parse()`.

```php
<?php
$Loader = (new josegonzalez\Dotenv\Loader('path/to/.env'))
              ->setFilters(['LollipopFilter'])
              ->parse()
              ->filter();
?>
```

#### Available Filters

The following filters are built into php-dotenv.

- `josegonzalez\Dotenv\Filter\LowercaseKeyFilter`: Lowercases all the keys for an environment to a single-depth.
- `josegonzalez\Dotenv\Filter\NullFilter`: Returns the environment data without any changes.
- `josegonzalez\Dotenv\Filter\UnderscoreArrayFilter`: Expands a flat array to a nested array. For example, `['0_Foo_Bar' => 'Far']` becomes `[['Foo' => ['Bar' => 'Far']]]`.
- `josegonzalez\Dotenv\Filter\UrlParseFilter`: When there is a key with the suffix `_URL`, this filter uses `parse_url` to add extra data to the environment.

### Static Environment Definition

You can also call it via the static `load` method call, which takes an array of arguments. If a method name is specified, the method is called with the value in the `$options` array being sent into the method.

```php
<?php
josegonzalez\Dotenv\Loader::load(array(
  'filepath' => 'path/to/.env',
  'expect' => array('FOO', 'BAR', 'BAZ'),
  'toEnv' => true,
  'toServer' => true,
  'define' => true,
);
?>
```

## Validating External Environments

In some cases it may be necessary to validate that a given array of environment data matches your requirements. You can use the `Loader->expect()` functionality via the standalone `Expect` class:

```php
<?php
use josegonzalez\Dotenv\Expect;

$expect = new Expect($env);
$expect('FOO'); // Raises a RuntimeException if `env` is missing FOO

// You can turn off exception raising using the second `raise` argument
$expect = new Expect($env, false);
$expect('FOO'); // Returns false if `env` is missing FOO
?>
```

## License

The MIT License (MIT)

Copyright (c) 2013 Jose Diaz-Gonzalez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
