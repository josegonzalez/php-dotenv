<?php

namespace josegonzalez\Dotenv\Filter;

class Filter
{
    /**
     * Returns the environment data without any changes.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param null|array<mixed, mixed> $config Config values.
     * @return array<string, mixed>
     */
    public function __invoke(array $environment, $config = null)
    {
        return $environment;
    }
}
