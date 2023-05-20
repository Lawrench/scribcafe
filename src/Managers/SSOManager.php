<?php

namespace App\Managers;

class SSOManager
{
    private SessionManager $sessionManager;
    private EnvironmentManager $environmentManager;
    private HttpManager $httpManager;

    public function __construct(
        SessionManager $sessionManager,
        EnvironmentManager $environmentManager,
        HttpManager $httpManager
    ) {
        $this->sessionManager = $sessionManager;
        $this->environmentManager = $environmentManager;
        $this->httpManager = $httpManager;
    }

    /**
     * Init SSO login
     * @return void
     */
    public function init(): void
    {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http'; // TODO: don't default to http
        $currentLocation = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if ($this->httpManager->isResponse()) {
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
        $login = $this->sessionManager->get('login');
        if ($login) {
            $this->httpManager->redirectTo($currentLocation);
        }

        $sso = $this->httpManager->getRequestParam('sso');
        $sig = $this->httpManager->getRequestParam('sig');

        // Validate the sso and sig parameters
        if (!preg_match('/^[a-zA-Z0-9+\/]+={0,2}$/', $sso) || !preg_match('/^[a-fA-F0-9]{64}$/', $sig)) {
            $this->httpManager->sendError(400);
        }

        // validate sso
        if (hash_hmac('sha256', urldecode($sso), $this->environmentManager->get('DISCOURSE_SSO_SECRET')) !== $sig) {
            $this->httpManager->sendError(400);
        }

        $sso = urldecode($sso);
        $query = [];
        parse_str(base64_decode($sso), $query);

        $nonce = $this->sessionManager->get('nonce');
        if ($query['nonce'] != $nonce) {
            $this->httpManager->sendError(400);
        }

        $this->sessionManager->set('login', $query);
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
        $login = $this->sessionManager->get('login');
        if ($login) {
            return;
        }

        $nonce = hash('sha512', mt_rand());
        $this->sessionManager->set('nonce', $nonce);

        $payload = base64_encode(
            http_build_query([
                'nonce' => $nonce,
                'return_sso_url' => $currentLocation,
            ])
        );

        $request = [
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, $this->environmentManager->get('DISCOURSE_SSO_SECRET')),
        ];

        $query = http_build_query($request);
        $url = sprintf('%s/session/sso_provider?%s', $this->environmentManager->get('DISCOURSE_URL'), $query);
        $this->httpManager->redirectTo($url);
    }
}
