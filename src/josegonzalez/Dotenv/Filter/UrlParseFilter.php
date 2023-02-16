<?php

namespace josegonzalez\Dotenv\Filter;

class UrlParseFilter extends Filter
{
    /**
     * When there is a key with the suffix `_URL`, this filter uses `parse_url`
     * to add extra data to the environment.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param null|array<mixed, mixed> $config Config values. Here to be compatible with Filter.
     * @return array<string, mixed>
     */
    public function __invoke(array $environment, $config = null)
    {
        $newEnvironment = [];
        foreach ($environment as $key => $value) {
            $newEnvironment[$key] = $value;
            if (substr($key, -4) === '_URL') {
                $prefix = substr($key, 0, -3);
                $url = parse_url($value);
                if (is_array($url)) {
                    $newEnvironment[$prefix . 'SCHEME'] = $this->get($url, 'scheme', '');
                    $newEnvironment[$prefix . 'HOST'] = $this->get($url, 'host', '');
                    $newEnvironment[$prefix . 'PORT'] = $this->get($url, 'port', '');
                    $newEnvironment[$prefix . 'USER'] = $this->get($url, 'user', '');
                    $newEnvironment[$prefix . 'PASS'] = $this->get($url, 'pass', '');
                    $newEnvironment[$prefix . 'PATH'] = $this->get($url, 'path', '');
                    $newEnvironment[$prefix . 'QUERY'] = $this->get($url, 'query', '');
                    $newEnvironment[$prefix . 'FRAGMENT'] = $this->get($url, 'fragment', '');
                }
            }
        }
        return $newEnvironment;
    }

    /**
     * Gets the filter
     *
     * @param array<int|string, mixed> $data The array to look at
     * @param int|string $key The key in $data to look for
     * @param null|mixed $default The default value to return if key isn't found
     * @return null|mixed The found value or $default
     */
    public function get(array $data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }
}
