<?php

namespace Demo;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;

class App
{
    /**
     * @var League\OAuth2\Client\Provider\AbstractProvider
     */
    private $provider;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var League\OAuth2\Client\Token\AccessToken
     */
    private $accessToken;

    /**
     * @var GuzzleHttp\Client;
     */
    private $guzzleClient;

    /**
     * Ask the user to authorize this client to access their battle net account
     * This sends the user to battle net and if they approve the client we will
     * receive an authorization code that we can swap for an access token.
     */
    public function requestAuthorization()
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $this->provider->getAuthorizationUrl();

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $this->provider->getState();

        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);

        exit;
    }

    /**
     * Try to avoid CSRF
     */
    public function checkState()
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            $this->logger->warning(__METHOD__ . ' : Stored state not matching supplied state, possible CSRF attack');

            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            exit('Invalid state');
        }
    }

    /**
     * Swap the authorization code for an access token that our
     * client can use to access the users protected resources.
     */
    public function getAccessToken(string $authorizationCode)
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        try {

            // Try to get an access token using the authorization code grant.
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $authorizationCode
            ]);

            $this->setToken($accessToken);

            $accessTokenInfo = [
                'Access Token' => $accessToken->getToken(),
                'Refresh Token' => $accessToken->getRefreshToken(),
                'Expires in' => $accessToken->getExpires(),
                'Has already expired?' => ($accessToken->hasExpired() ? 'expired' : 'not expired')
            ];

            $this->logger->debug("Access token", $accessTokenInfo);

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

            // Failed to get the access token or user details.
            $this->logger->error(__METHOD__ . ' : [' . $e->getMessage() . ']');

            exit('Failed to get the access token or user details');

        }
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setToken(AccessToken $accessToken): void
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        $this->accessToken = $accessToken;
    }

    /**
     * @return array
     */
    public function getCharacterDetails(): array
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        $endpoint = '/wow/character/defias-brotherhood/php';

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken->getToken(),
            'Accept' => 'application/json',
        ];

        $response = $this->guzzleClient->request('GET', $endpoint, [
            'headers' => $headers
        ]);

        $textResponse = $response->getBody()->getContents();

        return json_decode($textResponse, true);
    }
    
    /**
     * Clear the session to allow us to retry authorizing
     */
    public function resetSession(): void
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        unset($_SESSION);

        session_destroy();

        exit('Reset the session');
    }

    /**
     * @param AbstractProvider $provider
     */
    public function setProvider(AbstractProvider $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param Client $guzzleClient
     */
    public function setGuzzleClient(Client $guzzleClient): void
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        $this->guzzleClient = $guzzleClient;
    }
}