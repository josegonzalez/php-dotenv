<?php

namespace josegonzalez\Dotenv;

use InvalidArgumentException;
use LogicException;
use RuntimeException;

class Loader
{

    protected $environment = null;

    protected $filepath = null;

    protected $skip = array(
        'define' => false,
        'toEnv' => false,
        'toServer' => false,
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
        }

        $dotenv = new \josegonzalez\Dotenv\Loader($filepath);
        $dotenv->parse();

        if (array_key_exists('skipExisting', $options)) {
            $dotenv->skipExisting($options['skipExisting']);
        }

        if (array_key_exists('expect', $options)) {
            $dotenv->expect($options['expect']);
        }

        if (array_key_exists('define', $options)) {
            $dotenv->define();
        }

        if (array_key_exists('toEnv', $options)) {
            $dotenv->toEnv($options['toEnv']);
        }

        if (array_key_exists('toServer', $options)) {
            $dotenv->toServer($options['toServer']);
        }

        if (array_key_exists('raiseExceptions', $options)) {
            $dotenv->raiseExceptions($options);
        }

        return $dotenv;
    }

    public function parse()
    {
        if (!file_exists($this->filepath)) {
            return $this->raise('InvalidArgumentException', sprintf("Environment file '%s' is not found.", $this->filepath));
        }

        if (!is_readable($this->filepath)) {
            return $this->raise('InvalidArgumentException', sprintf("Environment file '%s' is not readable.", $this->filepath));
        }

        $fc = file_get_contents($this->filepath);
        if ($fc === false) {
            return $this->raise('InvalidArgumentException', sprintf("Environment file '%s' is not readable.", $this->filepath));
        }

        $lines = explode(PHP_EOL, $fc);

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
            return $this->raise('InvalidArgumentException', 'No arguments were passed to expect()');
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
            return $this->raise('RuntimeException', sprintf("Required ENV vars missing: ['%s']", implode("', '", $missingEnvs)));
        }

        return $this;
    }

    public function define()
    {
        $this->requireParse('define');
        foreach ($this->environment as $key => $value) {
            if (defined($key)) {
                if ($this->skip['define']) {
                    continue;
                }

                return $this->raise('LogicException', sprintf('Key "%s" has already been defined', $key));
            }

            define($key, $value);
        }

        return $this;
    }

    public function toEnv($overwrite = false)
    {
        $this->requireParse('toEnv');
        foreach ($this->environment as $key => $value) {
            if (isset($_ENV[$key]) && !$overwrite) {
                if ($this->skip['toEnv']) {
                    continue;
                }

                return $this->raise('LogicException', sprintf('Key "%s" has already been defined in $_ENV', $key));
            }

            $_ENV[$key] = $value;
        }

        return $this;
    }

    public function toServer($overwrite = false)
    {
        $this->requireParse('toServer');
        foreach ($this->environment as $key => $value) {
            if (isset($_SERVER[$key]) && !$overwrite) {
                if ($this->skip['toServer']) {
                    continue;
                }

                return $this->raise('LogicException', sprintf('Key "%s" has already been defined in $_SERVER', $key));
            }

            $_SERVER[$key] = $value;
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

    public function raiseExceptions($raise = true)
    {
        $this->raise = $raise;
        return $this;
    }

    public function toArray()
    {
        $this->requireParse('toArray');
        return $this->environment;
    }

    public function __toString()
    {
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            return json_encode($this->toArray());
        } else {
            return json_encode($this->toArray(), JSON_PRETTY_PRINT);
        }
    }

    protected function requireParse($method)
    {
        if (!is_array($this->environment)) {
            return $this->raise('LogicException', sprintf('Environment must be parsed before calling %s()', $method));
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
