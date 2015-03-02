<?php

namespace josegonzalez\Dotenv;

class Parser
{
    public function parse($contents)
    {
        $lines = preg_split('/\r\n|\r|\n/', $contents);
        $environment = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if ($line[0] == '#') {
                continue;
            }

            if (!preg_match('/(?:export )?([a-zA-Z0-9_]*)(\s?)=(\s?)(.*)/', $line, $matches)) {
                continue;
            }

            $key = $matches[1];
            $value = $matches[4];

            if (preg_match('/^[0-9]/', $key) == 1) {
                continue;
            }

            if (!$value) {
                $value = '';
            } elseif (strpbrk($value[0], '"\'') !== false) {
                $quote = $value[0];
                $regexPattern = sprintf('/^
                    %1$s          # match a quote at the start of the value
                    (             # capturing sub-pattern used
                     (?:          # we do not need to capture this
                      [^%1$s\\\\] # any character other than a quote or backslash
                      |\\\\\\\\   # or two backslashes together
                      |\\\\%1$s   # or an escaped quote e.g \"
                     )*           # as many characters that match the previous rules
                    )             # end of the capturing sub-pattern
                    %1$s          # and the closing quote
                    .*$           # and discard any string after the closing quote
                    /mx', $quote);
                $value = preg_replace($regexPattern, '$1', $value);
                $value = str_replace("\\$quote", $quote, $value);
                $value = str_replace('\\\\', '\\', $value);
            } elseif (strpos($value, ' #') !== false) {
                $parts = explode(' #', $value, 2);
                $value = $parts[0];
            }

            $environment[$key] = trim($value);
            $environment[$key] = $this->processReplacements(trim($value), $environment);
        }

        return $environment;
    }

    public function processReplacements($value, $environment)
    {
        if (strpos($value, '$') !== false) {
            $value = preg_replace_callback(
                '/{\$([a-zA-Z0-9_]+)}/',
                function ($matchedPatterns) use ($environment) {
                    if (isset($environment[$matchedPatterns[1]])) {
                        return $environment[$matchedPatterns[1]];
                    }
                    return '{}';
                },
                $value
            );
        }
        return $value;
    }
}
