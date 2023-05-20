<?php

namespace App\Managers;

class SessionManager
{
    /**
     * Set key the session key
     * TODO: save key in database
     * @param  string  $key
     * @param  string|array  $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get the session key or null
     * TODO: get key from database
     * @param  string  $key
     * @return null|string
     */
    public function get(string $key): mixed
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }
}
