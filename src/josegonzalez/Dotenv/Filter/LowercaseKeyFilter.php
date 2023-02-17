<?php

namespace josegonzalez\Dotenv\Filter;

class LowercaseKeyFilter extends Filter
{
    /**
     * Lowercases all the keys for an environment to a single-depth.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param null|array<mixed, mixed> $config Config values. Here to be compatible with Filter.
     * @return array<string, mixed>
     */
    public function __invoke(array $environment, $config = null)
    {
        $newEnvironment = [];
        foreach ($environment as $key => $value) {
            $newEnvironment[strtolower($key)] = $value;
        }
        return $newEnvironment;
    }
}
