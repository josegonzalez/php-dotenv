<?php

namespace josegonzalez\Dotenv\Filter;

class UppercaseFirstKeyFilter
{
    /**
     * Uppercases the first letter for all the keys for an environment to a single-depth.
     *
     * @param array $data Array of environment data
     * @return array
     */
    public function __invoke(array $environment)
    {
        $newEnvironment = array();
        foreach ($environment as $key => $value) {
            $newEnvironment[ucfirst($key)] = $value;
        }
        return $newEnvironment;
    }
}
