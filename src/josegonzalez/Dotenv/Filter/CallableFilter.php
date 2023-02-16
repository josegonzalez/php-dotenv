<?php

namespace josegonzalez\Dotenv\Filter;

class CallableFilter extends Filter
{
    /**
     * Wraps a callable and invokes it upon the environment.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param array<mixed, mixed> $config Array of configuration data that includes the callable
     * @return array<string, mixed>|object
     */
    public function __invoke(array $environment, $config = [])
    {
        $callable = $config['callable'];
        return $callable($environment, $config);
    }
}
