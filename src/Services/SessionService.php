<?php

namespace App\Services;

class SessionService
{
    /**
     * Get the session
     * This enables testing by mocking the $_SESSION variable
     * @return array
     */
    protected function &getSession(): array
    {
        return $_SESSION;
    }

    /**
     * Get a session variable
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $session = &$this->getSession();

        if (isset($session[$key])) {
            return $session[$key];
        }

        return null;
    }

    /**
     * Set a session variable
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $session = &$this->getSession();
        $session[$key] = $value;
    }
}
