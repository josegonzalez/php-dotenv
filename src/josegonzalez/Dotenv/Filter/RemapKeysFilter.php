<?php

namespace josegonzalez\Dotenv\Filter;

class RemapKeysFilter extends Filter
{
    /**
     * Remaps specific keys in a $config array to a set of values at a single-depth.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param array<mixed, mixed> $config Config values.
     * @return array<mixed, mixed>
     */
    public function __invoke(array $environment, $config = [])
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $environment)) {
                $environment[$value] = $environment[$key];
                unset($environment[$key]);
            }
        }
        return $environment;
    }
}
