<?php

namespace App\Managers;

class SSOManager
{
    private $sessionManager;
    private $environmentManager;
    private $httpManager;

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
     * Redirect to discourse for login
     * @param  string  $currentLocation
     * @return void
     */
    public static function redirectAuth(string $currentLocation): void
    {
        // user is logged on
        $session = new SessionManager(); // TODO: set this as a member or dep. injection once the function is extracted
        $login = $session->get('login');
        if ($login) {
            return;
        }

        $nonce = hash('sha512', mt_rand());

        $session = new SessionManager(); // TODO: set this as a member or dep. injection once the function is extracted
        $session->set('nonce', $nonce);

        $payload = base64_encode(
            http_build_query([
                'nonce' => $nonce,
                'return_sso_url' => $currentLocation,
            ])
        );

        $env = new EnvironmentManager(); /// todo: class member, dependency injection
        $request = [
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, $env->get('DISCOURSE_SSO_SECRET')),
        ];

        $query = http_build_query($request);

        $url = sprintf('%s/session/sso_provider?%s', $env->get('DISCOURSE_URL'), $query);

        $httpManager = new HttpManager(); // TODO: dependency injection
        $httpManager->redirectTo($url);
    }

    /**
     * Validate the sso and log the user in
     * Sets the login key
     * @param  string  $currentLocation
     * @return void
     */
    public static function login(string $currentLocation): void
    {
        $httpManager = new HttpManager(); // TODO: dependency injection
        $session = new SessionManager(); // TODO: set this as a member or dep. injection once the function is extracted
        $login = $session->get('login');
        if ($login) {
            $httpManager = new HttpManager(); // TODO: dependency injection
            $httpManager->redirectTo($currentLocation);
        }

        $sso = $httpManager->getRequestParam('sso');
        $sig = $httpManager->getRequestParam('sig');

        // Validate the sso and sig parameters
        if (!preg_match('/^[a-zA-Z0-9+\/]+={0,2}$/', $sso) || !preg_match('/^[a-fA-F0-9]{64}$/', $sig)) {
            $httpManager->sendError(400);
        }

        // validate sso
        $env = new EnvironmentManager(); /// todo: class member, dependency injection
        if (hash_hmac('sha256', urldecode($sso), $env->get('DISCOURSE_SSO_SECRET')) !== $sig) {
            $httpManager->sendError(400);
        }

        $sso = urldecode($sso);
        $query = [];
        parse_str(base64_decode($sso), $query);

        $session = new SessionManager(); // TODO: set this as a member or dep. injection once the function is extracted
        $nonce = $session->get('nonce');
        if ($query['nonce'] != $nonce) {
            $httpManager->sendError(400);
        }

        $session = new SessionManager();
        $session->set('login', $query);
        $allowOrigin = getenv('DISCOURSE_URL');
        header("Access-Control-Allow-Origin: $allowOrigin");
    }

    /**
     * Init SSO login
     * @return void
     */
    public static function init(): void
    {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http'; // TODO: don't default to http
        $currentLocation = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if (HttpManager::isResponse()) {
            SSOManager::login($currentLocation);
        } else {
            SSOManager::redirectAuth($currentLocation);
        }
    }

    public function createSSORequest()
    { /* ... */
    }

    public function validateSSOResponse()
    { /* ... */
    }

    public function loginUser()
    { /* ... */
    }
}
