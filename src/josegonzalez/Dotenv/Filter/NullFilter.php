<?php

namespace josegonzalez\Dotenv\Filter;

class NullFilter extends Filter
{
    /**
     * Returns the environment data without any changes.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param null|array<mixed, mixed> $config Config values. Here to be compatible with Filter.
     * @return array<string, mixed>
     */
    public function __invoke(array $environment, $config = [])
    {
        return $environment;
    }
}
