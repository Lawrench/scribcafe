<?php

namespace App\Services;

class SSOService
{
    private SessionService $sessionService;
    private EnvironmentService $environmentService;
    private HttpService $httpService;

    public function __construct(
        SessionService $sessionService,
        EnvironmentService $environmentService,
        HttpService $httpService
    ) {
        $this->sessionService = $sessionService;
        $this->environmentService = $environmentService;
        $this->httpService = $httpService;
    }

    /**
     * Init SSO login
     * @return void
     */
    public function init(): void
    {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'https';
        $currentLocation = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if ($this->httpService->isResponse()) {
            $this->login($currentLocation);
        } else {
            $this->redirectAuth($currentLocation);
        }
    }

    /**
     * Validate the sso and log the user in
     * Sets the login key
     * @param  string  $currentLocation
     * @return void
     */
    public function login(string $currentLocation): void
    {
        // check if user is logged in, if so redirect to current location
        $login = $this->sessionService->get('login');
        if ($login) {
            $this->httpService->redirectTo($currentLocation);
        }

        // get sso and sig from the request
        $sso = $this->httpService->getRequestParam('sso');
        $sig = $this->httpService->getRequestParam('sig');

        // Validate the sso and sig parameters
        if (!preg_match('/^[a-zA-Z0-9+\/]+={0,2}$/', $sso) || !preg_match('/^[a-fA-F0-9]{64}$/', $sig)) {
            $this->httpService->sendError(400);
        }

        // validate sso
        if (hash_hmac('sha256', urldecode($sso), $this->environmentService->get('DISCOURSE_SSO_SECRET')) !== $sig) {
            $this->httpService->sendError(400);
        }

        // Decode the URL-encoded SSO parameter
        $sso = urldecode($sso);

        // Parse decoded SSO parameter into an associative array
        $query = [];
        parse_str(base64_decode($sso), $query);

        // Get nonce value from session
        $nonce = $this->sessionService->get('nonce');

        // Check if nonce value from SSO parameter matches session
        if ($query['nonce'] != $nonce) {
            // If nonce values don't match, send a 400 Bad Request response
            $this->httpService->sendError(400);
        }

        // Store SSO query parameters in session variable
        $this->sessionService->set('login', $query);

        // Set Access-Control-Allow-Origin header to allow requests from DISCOURSE_URL
        $allowOrigin = getenv('DISCOURSE_URL');
        header("Access-Control-Allow-Origin: $allowOrigin");
    }


    /**
     * Redirect to discourse for login
     * @param  string  $currentLocation
     * @return void
     */
    public function redirectAuth(string $currentLocation): void
    {
        // user is logged on
        $login = $this->sessionService->get('login');
        if ($login) {
            return;
        }

        $nonce = hash('sha512', mt_rand());
        $this->sessionService->set('nonce', $nonce);

        $payload = base64_encode(
            http_build_query([
                'nonce' => $nonce,
                'return_sso_url' => $currentLocation,
            ])
        );

        $request = [
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, $this->environmentService->get('DISCOURSE_SSO_SECRET')),
        ];

        $query = http_build_query($request);
        $url = sprintf('%s/session/sso_provider?%s', $this->environmentService->get('DISCOURSE_URL'), $query);
        $this->httpService->redirectTo($url);
    }
}
