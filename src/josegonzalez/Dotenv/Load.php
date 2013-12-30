<?php

namespace josegonzalez\Dotenv;

use InvalidArgumentException;
use LogicException;
use JsonSerializable;
use RuntimeException;

class Load implements JsonSerializable
{

    protected $_filepath = null;

    protected $_environment = null;

    public function __construct($filepath = null) {
        $this->setFilepath($filepath);
        return $this;
    }

    public function filepath() {
        return $this->_filepath;
    }

    public function setFilepath($filepath = null) {
        if ($filepath == null) {
            $filepath = __DIR__ . DIRECTORY_SEPARATOR . '.env';
        }
        $this->_filepath = $filepath;
        return $this;
    }

    public static function load($options = null) {
        $filepath = null;
        if (is_string($options)) {
            $filepath = $options;
            $options = array();
        }

        $dotenv = new \josegonzalez\Dotenv\Load($filepath);
        $dotenv->parse();
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

        return $dotenv;
    }

    public function parse() {
        if (!file_exists($this->_filepath)) {
            throw new InvalidArgumentException(sprintf("Environment file '%s' is not found.", $this->_filepath));
        }

        if (!is_readable($this->_filepath)) {
            throw new InvalidArgumentException(sprintf("Environment file '%s' is not readable.", $this->_filepath));
        }

        $fc = file_get_contents($this->_filepath);
        if ($fc === false) {
            throw new InvalidArgumentException(sprintf("Environment file '%s' is not readable.", $this->_filepath));
        }

        $lines = explode(PHP_EOL, $fc);

        $this->_environment = array();
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

            $this->_environment[$key] = $value;
        }

        return $this;
    }

    public function expect() {
        $this->requireParse('expect');

        $args = func_get_args();
        if (count($args) == 0) {
            throw new InvalidArgumentException("No arguments were passed to expect()");
        }

        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        $keys = (array) $args;
        $missingEnvs = array();

        foreach ($keys as $key) {
            if (!isset($this->_environment[$key])) {
                $missingEnvs[] = $key;
            }
        }

        if (!empty($missingEnvs)) {
            throw new RuntimeException(sprintf("Required ENV vars missing: ['%s']", implode("', '", $missingEnvs)));
        }

        return $this;
    }

    public function define() {
        $this->requireParse('define');
        foreach ($this->_environment as $key => $value) {
            if (defined($key)) {
                throw new LogicException(sprintf('Key "%s" has already been defined', $key));
            }

            define($key, $value);
        }

        return $this;
    }

    public function toEnv($overwrite = false) {
        $this->requireParse('toEnv');
        foreach ($this->_environment as $key => $value) {
            if (isset($_ENV[$key]) && !$overwrite) {
                throw new LogicException(sprintf('Key "%s" has already been defined in $_ENV', $key));
            }

            $_ENV[$key] = $value;
        }

        return $this;
    }

    public function toServer($overwrite = false) {
        $this->requireParse('toServer');
        foreach ($this->_environment as $key => $value) {
            if (isset($_SERVER[$key]) && !$overwrite) {
                throw new LogicException(sprintf('Key "%s" has already been defined in $_SERVER', $key));
            }

            $_SERVER[$key] = $value;
        }

        return $this;
    }

    public function toArray() {
        $this->requireParse('environment');
        return $this->_environment;
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    public function __toString() {
        return json_encode($this, JSON_PRETTY_PRINT);
    }

    protected function requireParse($method) {
        if (!is_array($this->_environment)) {
            throw new LogicException(sprintf('Environment must be parsed before calling %s', $method));
        }
    }
}
