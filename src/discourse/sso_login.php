<?php

namespace App\Discourse;

use Dotenv\Dotenv;

/**
 * Class SSOLogin
 * Single sign-on authentication via Discourse
 * Saves the login key
 * @package App\Discourse
 */
class SSOLogin
{

    /**
     * Init SSO login
     * @return void
     */
    public static function init(): void
    {
        $currentLocation = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if (self::isResponse()) {
            self::login($currentLocation);
        } else {
            self::redirectAuth($currentLocation);
        }
    }

    /**
     * Get key
     * TODO: get key from database
     * @param  string  $key
     * @return mixed
     */
    private static function getKey(string $key): mixed
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    /**
     * Check if this is a response from discourse
     * @return bool
     */
    private static function isResponse(): bool
    {
        return !empty($_GET) && isset($_GET['sso']);
    }

    /**
     * Validate the sso and log the user in
     * Sets the login key
     * @param  string  $currentLocation
     * @return void
     */
    private static function login(string $currentLocation): void
    {
        $login = self::getKey('login');
        if ($login) {
            header("Location: $currentLocation");
            die();
        }

        $sso = $_GET['sso'] ?? '';
        $sig = $_GET['sig'] ?? '';

        // Validate the sso and sig parameters
        if (!preg_match('/^[a-zA-Z0-9+\/]+={0,2}$/', $sso) || !preg_match('/^[a-fA-F0-9]{64}$/', $sig)) {
            header('HTTP/1.1 400 Bad Request');
            die('Invalid parameters.');
        }

        // validate sso
        if (hash_hmac('sha256', urldecode($sso), $_ENV('DISCOURSE_SSO_SECRET') ?? '') !== $sig) {
            header("HTTP/1.1 400 Bad Request");
            die('Invalid SSO Authentication');
        }

        $sso = urldecode($sso);
        $query = [];
        parse_str(base64_decode($sso), $query);

        $nonce = self::getKey('nonce');
        if ($query['nonce'] != $nonce) {
            header("HTTP/1.1 400 Bad Request");
            die();
        }

        self::setKey('login', $query);
        $allowOrigin = getenv('DISCOURSE_URL');
        header("Access-Control-Allow-Origin: $allowOrigin");
    }

    /**
     * Redirect to discourse for login
     * @param  string  $currentLocation
     * @return void
     */
    private static function redirectAuth(string $currentLocation): void
    {
        // user is logged on
        $login = self::getKey('login');
        if ($login) {
            return;
        }

        $nonce = hash('sha512', mt_rand());
        self::setKey('nonce', $nonce);

        $payload = base64_encode(
            http_build_query([
                'nonce' => $nonce,
                'return_sso_url' => $currentLocation,
            ])
        );

        $request = [
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, $_ENV('DISCOURSE_SSO_SECRET') ?? ''),
        ];

        $query = http_build_query($request);

        $url = sprintf('%s/session/sso_provider?%s', $_ENV('DISCOURSE_URL') ?? '', $query);
        header("Location: $url");
        die();
    }

    /**
     * Set key
     * TODO: save key in database
     * @param  string  $key
     * @param  string|array  $value
     * @return void
     */
    private static function setKey(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
}
