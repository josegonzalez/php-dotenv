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

            if (strlen($value) === 0) {
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
                $value = $this->processQuotedValue($value, $environment);

                if (!empty($value) && strpbrk($value[0], '"\'') !== false) {
                    $quote = $value[0];
                    $value = preg_replace($regexPattern, '$1', $value);
                    $value = str_replace("\\$quote", $quote, $value);
                    $value = str_replace('\\\\', '\\', $value);
                }
            } else {
                $value = $this->processUnquotedValue($value);
            }

            $environment[$key] = $value;
        }

        return $environment;
    }

    public function processUnquotedValue($value)
    {
        $parts = explode(' ', trim($value), 2);
        $value = $parts[0];
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        } elseif (ctype_digit($value)) {
            $value = (int)$value;
        }

        return $value;
    }

    public function processQuotedValue($value, $environment)
    {
        if (strpos($value, '\\n') !== false) {
            $value = str_replace('\\n', "\n", $value);

            $lines = explode("\n", $value);
            $count = count($lines);
            $lastLine = explode(' #', $lines[$count - 1], 2);
            $lines[$count - 1] = $lastLine[0];
            $value = implode("\n", $lines);
        }

        if (strpos($value, '$') !== false) {
            $value = preg_replace_callback(
                '/\${([a-zA-Z0-9_]+)}/',
                function ($matchedPatterns) use ($environment) {
                    if (isset($environment[$matchedPatterns[1]])) {
                        return $environment[$matchedPatterns[1]];
                    }
                    return '{}';
                },
                $value
            );
        }

        if ($value === "''" || $value === '""') {
            $value = '';
        }

        return $value;
    }
}
