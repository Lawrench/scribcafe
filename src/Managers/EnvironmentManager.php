<?php

namespace App\Managers;

/**
 * Class EnvironmentManager
 * @package App\Managers
 * manage environment variables.
 */
class EnvironmentManager
{
    /**
     * gets the value of an environment variable
     * @param  string  $name  name of environment variable
     * @return string|null the environment variable, or null
     */
    public function get(string $name): ?string
    {
        return $_ENV[$name] ?? getenv($name) ?: null;
    }
}
