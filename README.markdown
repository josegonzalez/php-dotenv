[![Build Status](https://travis-ci.org/josegonzalez/php-dotenv.png?branch=master)](https://travis-ci.org/josegonzalez/php-dotenv) [![Coverage Status](https://coveralls.io/repos/josegonzalez/php-dotenv/badge.png?branch=master)](https://coveralls.io/r/josegonzalez/php-dotenv?branch=master) [![Total Downloads](https://poser.pugx.org/josegonzalez/dotenv/d/total.png)](https://packagist.org/packages/josegonzalez/dotenv) [![Latest Stable Version](https://poser.pugx.org/josegonzalez/dotenv/v/stable.png)](https://packagist.org/packages/josegonzalez/dotenv)

# PHP Dotenv

`.env` file parsing for PHP

## Requirements

* PHP 5.3+

## Installation

_[Using [Composer](http://getcomposer.org/)]_

Add the plugin to your project's `composer.json` - something like this:

```composer
  {
    "require": {
      "josegonzalez/dotenv": "dev-master"
    }
  }
```

## Usage

> Example `.env` files are available in the [fixtures](https://github.com/josegonzalez/php-dotenv/tree/master/tests/josegonzalez/fixtures) directory.

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
              ->parse()
              ->raiseExceptions(false)
              ->expect('FOO', 'BAR', 'BAZ'); // Returns false if variables are missing
?>
```

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
