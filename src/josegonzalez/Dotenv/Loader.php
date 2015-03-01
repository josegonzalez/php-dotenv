<?php

namespace josegonzalez\Dotenv;

use InvalidArgumentException;
use LogicException;
use RuntimeException;

class Loader
{

    protected $environment = null;

    protected $filepath = null;

    protected $prefix = null;

    protected $raise = true;

    protected $skip = array(
        'define' => false,
        'toEnv' => false,
        'toServer' => false,
        'putenv' => false
    );

    public function __construct($filepath = null)
    {
        $this->setFilepath($filepath);
        return $this;
    }

    public function filepath()
    {
        return $this->filepath;
    }

    public function setFilepath($filepath = null)
    {
        if ($filepath == null) {
            $filepath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
        }
        $this->filepath = $filepath;
        return $this;
    }

    public static function load($options = null)
    {
        $filepath = null;
        if (is_string($options)) {
            $filepath = $options;
            $options = array();
        } elseif (isset($options['filepath'])) {
            $filepath = $options['filepath'];
            unset($options['filepath']);
        }

        $dotenv = new \josegonzalez\Dotenv\Loader($filepath);

        if (array_key_exists('raiseExceptions', $options)) {
            $dotenv->raiseExceptions($options['raiseExceptions']);
        }

        $dotenv->parse();

        $methods = array(
            'skipExisting',
            'prefix',
            'expect',
            'define',
            'toEnv',
            'toServer',
            'putenv',
        );
        foreach ($methods as $method) {
            if (array_key_exists($method, $options)) {
                $dotenv->$method($options[$method]);
            }
        }

        return $dotenv;
    }

    public function parse()
    {
        if (!file_exists($this->filepath)) {
            return $this->raise(
                'InvalidArgumentException',
                sprintf("Environment file '%s' is not found", $this->filepath)
            );
        }

        if (is_dir($this->filepath)) {
            return $this->raise(
                'InvalidArgumentException',
                sprintf("Environment file '%s' is a directory. Should be a file", $this->filepath)
            );
        }

        if (!is_readable($this->filepath)) {
            return $this->raise(
                'InvalidArgumentException',
                sprintf("Environment file '%s' is not readable", $this->filepath)
            );
        }

        $contents = file_get_contents($this->filepath);
        if ($contents === false) {
            return $this->raise(
                'InvalidArgumentException',
                sprintf("Environment file '%s' is not readable", $this->filepath)
            );
        }

        $lines = preg_split('/\r\n|\r|\n/', $contents);

        $this->environment = array();
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if (!preg_match('/(?:export )?([a-zA-Z_][a-zA-Z0-9_]*)=(.*)/', $line, $matches)) {
                continue;
            }

            $key = $matches[1];
            $value = $matches[2];
            if (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            }

            $this->environment[$key] = $value;
        }

        return $this;
    }

    public function expect()
    {
        $this->requireParse('expect');

        $args = func_get_args();
        if (count($args) == 0) {
            return $this->raise('LogicException', 'No arguments were passed to expect()');
        }

        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        $keys = (array) $args;
        $missingEnvs = array();

        foreach ($keys as $key) {
            if (!isset($this->environment[$key])) {
                $missingEnvs[] = $key;
            }
        }

        if (!empty($missingEnvs)) {
            return $this->raise(
                'RuntimeException',
                sprintf("Required ENV vars missing: ['%s']", implode("', '", $missingEnvs))
            );
        }

        return $this;
    }

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

    public function toEnv($overwrite = false)
    {
        $this->requireParse('toEnv');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (isset($_ENV[$prefixedKey]) && !$overwrite) {
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

    public function putenv($overwrite = false)
    {
        $this->requireParse('putenv');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (getenv($prefixedKey) && !$overwrite) {
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

    public function toServer($overwrite = false)
    {
        $this->requireParse('toServer');
        foreach ($this->environment as $key => $value) {
            $prefixedKey = $this->prefixed($key);
            if (isset($_SERVER[$prefixedKey]) && !$overwrite) {
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

        foreach ((array)$types as $type) {
            $this->skip[$type] = true;
        }

        return $this;
    }

    public function skipped()
    {
        $skipped = array();
        foreach ($this->skip as $key => $value) {
            if ($value == true) {
                $skipped[] = $key;
            }
        }
        return $skipped;
    }

    public function prefix($prefix = null)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function prefixed($key)
    {
        if (!!$this->prefix) {
            $key = $this->prefix . $key;
        }

        return $key;
    }

    public function raiseExceptions($raise = true)
    {
        $this->raise = $raise;
        return $this;
    }

    public function toArray()
    {
        $this->requireParse('toArray');
        if ($this->environment === null) {
            return null;
        }

        $environment = array();
        foreach ($this->environment as $key => $value) {
            $environment[$this->prefixed($key)] = $value;

        }
        return $environment;
    }

    public function __toString()
    {
        try {
            $data = $this->toArray();
        } catch (LogicException $e) {
            $data = array();
        }

        return json_encode($data);
    }

    protected function requireParse($method)
    {
        if (!is_array($this->environment)) {
            return $this->raise(
                'LogicException',
                sprintf('Environment must be parsed before calling %s()', $method)
            );
        }
    }

    protected function raise($exception, $message)
    {
        if ($this->raise) {
            throw new $exception($message);
        }

        return false;
    }
}
