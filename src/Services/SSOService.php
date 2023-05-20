<?php

namespace App\Services;

class SSOService
{
    private SessionService $sessionService;
    private EnvironmentService $environmentService;
    private HttpService $httpService;

    public function __construct(
        SessionService $sessionManager,
        EnvironmentService $environmentManager,
        HttpService $httpManager
    ) {
        $this->sessionService = $sessionManager;
        $this->environmentService = $environmentManager;
        $this->httpService = $httpManager;
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
        $login = $this->sessionService->get('login');
        if ($login) {
            $this->httpService->redirectTo($currentLocation);
        }

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

        $sso = urldecode($sso);
        $query = [];
        parse_str(base64_decode($sso), $query);

        $nonce = $this->sessionService->get('nonce');
        if ($query['nonce'] != $nonce) {
            $this->httpService->sendError(400);
        }

        $this->sessionService->set('login', $query);
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
