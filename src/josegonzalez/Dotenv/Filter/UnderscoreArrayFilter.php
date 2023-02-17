<?php

namespace josegonzalez\Dotenv\Filter;

class UnderscoreArrayFilter extends Filter
{
    /**
     * Expands a flat array to a nested array.
     *
     * For example, `['0_Foo_Bar' => 'Far']` becomes
     * `[['Foo' => ['Bar' => 'Far']]]`.
     *
     * @param array<string, mixed> $environment Array of environment data
     * @param null|array<mixed, mixed> $config Config values. Here to be compatible with Filter.
     * @return array<string, mixed>
     */
    public function __invoke(array $environment, $config = null)
    {
        $result = [];
        foreach ($environment as $flat => $value) {
            $keys = explode('_', $flat);
            $keys = array_reverse($keys);
            $child = [
                $keys[0] => $value
            ];
            array_shift($keys);
            foreach ($keys as $k) {
                $child = [
                    $k => $child
                ];
            }

            $stack = [[$child, &$result]];
            while (!empty($stack)) {
                foreach ($stack as $curKey => &$curMerge) {
                    foreach ($curMerge[0] as $key => &$val) {
                        $hasKey = !empty($curMerge[1][$key]);
                        if ($hasKey && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                            $stack[] = [&$val, &$curMerge[1][$key]];
                        } else {
                            $curMerge[1][$key] = $val;
                        }
                    }
                    unset($stack[$curKey]);
                }
                unset($curMerge);
            }
        }
        return $result;
    }
}
