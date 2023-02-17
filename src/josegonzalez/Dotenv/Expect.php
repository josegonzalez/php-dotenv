<?php

namespace josegonzalez\Dotenv;

use LogicException;
use RuntimeException;

class Expect
{
    protected $environment = [];

    protected $raise = true;


    /**
     * Checks the environment ans throws an exception of something is missing.
     *
     * @param array<int|string, mixed> $environment The variables to check
     * @param bool $raise If we should throw exceptions or not.
     * @return void
     */
    public function __construct(array $environment, $raise = true)
    {
        $this->environment = $environment;
        $this->raise = $raise;
    }

    /**
     * Checks the environment ans throws an exception of something is missing.
     *
     * @return bool Actually always false
     * @throws \RuntimeException|\LogicException
     */
    public function __invoke()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            return $this->raise('LogicException', 'No arguments were passed to expect()');
        }

        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }

        $keys = (array) $args;
        $missingEnvs = [];

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

        return true;
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

        return false;
    }
}
