<?php
/**
 * The class that loads the variables from the .env file
 * and adds them to the needed enviroments.
 */

namespace josegonzalez\Dotenv;

use InvalidArgumentException;
use josegonzalez\Dotenv\Expect;
use josegonzalez\Dotenv\Filter\CallableFilter;
use LogicException;
use M1\Env\Parser;

class Loader
{
    /** @var array<int|string, object> List of thrown exceptsions being tracked. */
    protected $exceptions = [];

    /** @var null|array<string, mixed> The parsed variables. */
    protected $environment = null;

    protected $exceptions = array();

    protected $filepaths = null;

    /** @var array<string, mixed> List of filters to use. */
    protected $filters = [];

    /** @var null|string The prefix we should use for keys. */
    protected $prefix = null;

    /** @var bool If we should raise exceptions. */
    protected $raise = true;

    /** @var array<string, bool> Available sources and if they should be skipped. */
    protected $skip = [
        'apacheSetenv' => false,
        'define' => false,
        'putenv' => false,
        'toEnv' => false,
        'toServer' => false,
    ];

    /**
     * Constructor
     *
     * @param null|string|array<int|string, string> $filepaths
     * @return void
     */
    public function __construct($filepaths = null)
    {
        $this->setFilepaths($filepaths);
    }

    /**
     * Creates the Loader object with a set of options.
     *
     * @param null|string|array<int|string, mixed> $options
     * @return \josegonzalez\Dotenv\Loader
     */
    public static function load($options = null)
    {
        $filepaths = null;
        if (is_string($options)) {
            $filepaths = $options;
            $options = [];
        } elseif (isset($options['filepath'])) {
            $filepaths = (array)$options['filepath'];
            unset($options['filepath']);
        } elseif (isset($options['filepaths'])) {
            $filepaths = $options['filepaths'];
            unset($options['filepaths']);
        }

        $dotenv = new \josegonzalez\Dotenv\Loader($filepaths);

        if (array_key_exists('raiseExceptions', $options)) {
            $dotenv->raiseExceptions($options['raiseExceptions']);
        }

        $dotenv->parse();

        if (array_key_exists('filters', $options)) {
            $dotenv->setFilters($options['filters']);
            $dotenv->filter();
        }

        $methods = [
            'skipExisting',
            'prefix',
            'expect',
            'apacheSetenv',
            'define',
            'putenv',
            'toEnv',
            'toServer',
        ];

        foreach ($methods as $method) {
            if (array_key_exists($method, $options)) {
                $dotenv->$method($options[$method]);
            }
        }

        return $dotenv;
    }

    /**
     * Gets the current file path from the list.
     *
     * @return string
     */
    public function filepath()
    {
        return current($this->filepaths);
    }

    /**
     * Gets the list of files paths.
     *
     * @return array<int|string, string>|null
     */
    public function filepaths()
    {
        return $this->filepaths;
    }

    /**
     * Sets a single file path
     *
     * @param null|string $filepath The path to add to the list.
     * @return self
     */
    public function setFilepath(?string $filepath = null)
    {
        return $this->setFilepaths($filepath);
    }

    /**
     * Sets the list of paths to look for the .env file
     *
     * @param null|string|array<int|string, string> $filepaths One or more file paths to look in.
     * @return self
     */
    public function setFilepaths($filepaths = null)
    {
        if ($filepaths == null) {
            $filepaths = [__DIR__ . DIRECTORY_SEPARATOR . '.env'];
        }

        if (is_string($filepaths)) {
            $filepaths = [$filepaths];
        }

        $this->filepaths = $filepaths;
        return $this;
    }

    /**
     * gets the filters to use.
     *
     * @return array<string, mixed> The defined list of filters.
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Sets the filters to use.
     *
     * @param array<int|string, mixed> $filters An array of filters to use.
     * @return self|bool
     * @throws \LogicException
     */
    public function setFilters(array $filters)
    {
        $newList = [];
        $keys = array_keys($filters);
        $count = count($keys);
        for ($i = 0; $i < $count; $i++) {
            if (is_int($keys[$i])) {
                $filter = $filters[$keys[$i]];
                if (is_string($filter)) {
                    $newList[$filter] = null;
                } else {
                    $newList['__callable__' . $i] = [
                        'callable' => $filter
                    ];
                }
            } else {
                $newList[$keys[$i]] = $filters[$keys[$i]];
            }
        }

        $this->filters = $newList;

        foreach ($this->filters as $filterClass => $config) {
            if (substr($filterClass, 0, 12) === '__callable__') {
                if (is_callable($config['callable'])) {
                    continue;
                }
                return $this->raise(
                    'LogicException',
                    sprintf('Invalid filter class')
                );
            }
            if (is_callable($filterClass)) {
                continue;
            }
            if (!class_exists($filterClass)) {
                return $this->raise(
                    'LogicException',
                    sprintf('Invalid filter class %s', $filterClass)
                );
            }
            continue;
        }
        return $this;
    }

    /**
     * Filter variables
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function filter()
    {
        $this->requireParse('filter');

        $environment = $this->environment;
        foreach ($this->filters as $filterClass => $config) {
            $filter = $filterClass;
            if (is_string($filterClass)) {
                if (substr($filterClass, 0, 12) === '__callable__') {
                    $filter = new CallableFilter;
                }
                if (class_exists($filterClass)) {
                    $filter = new $filterClass;
                }
            }
            /** @var \josegonzalez\Dotenv\Filter\Filter $filter */
            $environment = $filter($environment, $config);
        }

        $this->environment = $environment;
        return $this;
    }

    /**
     * Parses the .env file into the environment variable
     *
     * @return self|bool
     * @throws \InvalidArgumentException
     */
    public function parse()
    {
        $contents = false;
        $filepaths = $this->filepaths();

        foreach ($filepaths as $i => $filepath) {
            $isLast = count($filepaths) - 1 === $i;
            if (!file_exists($filepath) && $isLast) {
                return $this->raise(
                    'InvalidArgumentException',
                    sprintf("Environment file '%s' is not found", $filepath)
                );
            }

            if (is_dir($filepath) && $isLast) {
                return $this->raise(
                    'InvalidArgumentException',
                    sprintf("Environment file '%s' is a directory. Should be a file", $filepath)
                );
            }

            if ((!is_readable($filepath) || ($contents = file_get_contents($filepath)) === false) && $isLast) {
                return $this->raise(
                    'InvalidArgumentException',
                    sprintf("Environment file '%s' is not readable", $filepath)
                );
            }

            if ($contents !== false) {
                break;
            }
        }

        /** @var string $contents Come on phpstan. */
        $parser = new Parser($contents);
        $this->environment = $parser->getContent();

        return $this;
    }

    /**
     * Used to create an Expect object
     *
     * @return self|bool
     * @throws \Exception
     */
    public function expect()
    {
        $this->requireParse('expect');

        $expect = new Expect($this->environment, $this->raise);
        call_user_func_array($expect, func_get_args());

        return $this;
    }

    /**
     * Uses apache_setenv() to set variables
     *
     * @param bool $overwrite If we should overwrite variables via apache_setenv()
     * @return self|bool
     * @throws \Exception
     */
    public function apacheSetenv($overwrite = false)
    {
        $this->requireParse('apache_setenv');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (apache_getenv($prefixedKey) !== false && !$overwrite) {
                if ($this->skip['apacheSetenv']) {
                    continue;
                }

                return $this->raise(
                    'LogicException',
                    sprintf('Key "%s" has already been defined in apache_getenv()', $prefixedKey)
                );
            }

            apache_setenv($prefixedKey, $value);
        }

        return $this;
    }

    /**
     * Uses define() to set variables
     *
     * @return self|bool
     * @throws \Exception
     */
    public function define()
    {
        $this->requireParse('define');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (defined($prefixedKey)) {
                if ($this->skip['define']) {
                    continue;
                }

                return $this->raise(
                    'LogicException',
                    sprintf('Key "%s" has already been defined', $prefixedKey)
                );
            }

            define($prefixedKey, $value);
        }

        return $this;
    }

    /**
     * Uses putenv() to set variables
     *
     * @param bool $overwrite If we should overwrite variables via putenv()
     * @return self|bool
     * @throws \Exception
     */
    public function putenv($overwrite = false)
    {
        $this->requireParse('putenv');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (getenv($prefixedKey) !== false && !$overwrite) {
                if ($this->skip['putenv']) {
                    continue;
                }

                return $this->raise(
                    'LogicException',
                    sprintf('Key "%s" has already been defined in getenv()', $prefixedKey)
                );
            }

            putenv($prefixedKey . '=' . $value);
        }

        return $this;
    }

    /**
     * Updates $_ENV
     *
     * @param bool $overwrite If we should overwrite the initial value of $_ENV values
     * @return self|bool
     * @throws \Exception
     */
    public function toEnv($overwrite = false)
    {
        $this->requireParse('toEnv');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (array_key_exists($prefixedKey, $_ENV) && !$overwrite) {
                if ($this->skip['toEnv']) {
                    continue;
                }

                return $this->raise(
                    'LogicException',
                    sprintf('Key "%s" has already been defined in $_ENV', $prefixedKey)
                );
            }

            $_ENV[$prefixedKey] = $value;
        }

        return $this;
    }

    /**
     * Updates $_SERVER
     *
     * @param bool $overwrite If we should overwrite the initial value of $_SERVER values
     * @return self|bool
     * @throws \Exception
     */
    public function toServer($overwrite = false)
    {
        $this->requireParse('toServer');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (array_key_exists($prefixedKey, $_SERVER) && !$overwrite) {
                if ($this->skip['toServer']) {
                    continue;
                }

                return $this->raise(
                    'LogicException',
                    sprintf('Key "%s" has already been defined in $_SERVER', $prefixedKey)
                );
            }

            $_SERVER[$prefixedKey] = $value;
        }

        return $this;
    }

    /**
     * Sets what types to skip
     *
     * @param mixed $types The types to skip
     * @return self
     */
    public function skipExisting($types = null)
    {
        $args = func_get_args();
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        $types = (array)$args;
        if (empty($types)) {
            $types = array_keys($this->skip);
        }

        /** @var string $type */
        foreach ((array)$types as $type) {
            $this->skip[$type] = true;
        }

        return $this;
    }

    /**
     * Returns list of variables to skip.
     *
     * @return array<int, string>
     */
    public function skipped()
    {
        $skipped = [];
        foreach ($this->skip as $key => $value) {
            if ($value == true) {
                $skipped[] = $key;
            }
        }
        return $skipped;
    }

    /**
     * Sets the prefix.
     *
     * @param string|null $prefix
     * @return self
     */
    public function prefix(?string $prefix = null)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Add prefix to a key
     *
     * @param string $key The key that may need to be prefixed.
     * @return string The modified key.
     */
    public function prefixed(string $key)
    {
        if (!!$this->prefix) {
            $key = $this->prefix . $key;
        }

        return $key;
    }

    /**
     * Flags if we should raise exceptions, or ignore them
     *
     * @param bool $raise If we should allow exceptions
     * @return self
     */
    public function raiseExceptions($raise = true)
    {
        $this->raise = $raise;
        return $this;
    }

    /**
     * Creates and return an array from the known environment.
     *
     * @return array<string, mixed>|null
     * @throws \Exception
     */
    public function toArray()
    {
        $this->requireParse('toArray');
        if ($this->environment === null) {
            return null;
        }

        $environment = [];
        foreach ($this->environment as $key => $value) {
            $environment[$this->prefixed($key)] = $value;
        }
        return $environment;
    }

    /**
     * Converts array to json string
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $data = $this->toArray();
        } catch (LogicException $e) {
            $data = [];
        }

        $json = json_encode($data);
        return $json ? $json : '';
    }

    /**
     * Requires the the .env has been parsed first.
     *
     * @param string $method The message send with the exception.
     * @return void|bool
     * @throws \Exception
     */
    protected function requireParse(string $method)
    {
        if (!is_array($this->environment)) {
            return $this->raise(
                'LogicException',
                sprintf('Environment must be parsed before calling %s()', $method)
            );
        }
    }

    /**
     * Throw and track exceptions
     *
     * @param \Exception|string $exception
     * @param string $message The message send with the exception.
     * @return bool Actually always false
     * @throws \Exception
     */
    protected function raise($exception, string $message): bool
    {
        if ($this->raise) {
            // @todo Figure out how to properly define this line so phpstan can understand it.
            // @phpstan-ignore-next-line
            throw new $exception($message);
        }


        $this->exceptions[] = new $exception($message);
        return false;
    }
}
