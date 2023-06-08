<?php

namespace App\Services;

class RequestService
{
    /**
     * Get a specific key-value from the $_SERVER superglobal.
     * @codeCoverageIgnore no business logic, just a wrapper.
     * @param  string  $key
     * @param  mixed  $default
     * @return string|null null if not found
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}
