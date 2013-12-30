<?php
namespace josegonzalez\Dotenv;

/**
 * For compatibility with PHP 5.3.
 */
interface JsonSerializable
{
    /**
     * @return array
     */
    public function jsonSerialize();
}
