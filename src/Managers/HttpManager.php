<?php

namespace App\Managers;

use JetBrains\PhpStorm\NoReturn;

class HttpManager
{
    /**
     * Get the value of a request parameter, with an optional default value.
     *
     * @param  string  $name  The name of the request parameter.
     * @param  string  $default  The default value to return if the request parameter is not set.
     * @return string The value of the request parameter, or the default value.
     */
    public function getRequestParam(string $name, string $default = ''): string
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * Check if this is a response from discourse
     * @return bool
     */
    public function isResponse(): bool
    {
        return !empty($_GET) && isset($_GET['sso']);
    }

    /**
     * @param  string  $url
     * @return void
     */
    #[NoReturn] public function redirectTo(string $url): void
    {
        http_response_code(302);
        header("Location: $url");
        exit;
    }

    /**
     * Send an HTTP error status code and message
     *
     * @param  int  $code  The HTTP status code to send.
     * @param  string  $message  An optional message to include in the response body.
     *
     * @return void
     */
    #[NoReturn] public function sendError(int $code, string $message = ''): void
    {
        http_response_code($code);

        if ($message) {
            echo $message;
        }

        exit;
    }
}
