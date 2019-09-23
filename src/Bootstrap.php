<?php

namespace Demo;

use Depotwarehouse\OAuth2\Client\Provider\WowProvider;
use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Dotenv\Dotenv;

class Bootstrap
{
    /**
     * Use dotenv library to set up the config values
     */
    public static function loadConfig()
    {
        // make configuration available
        $dotenv = Dotenv::create(__DIR__ . DIRECTORY_SEPARATOR . '..');

        $dotenv->load();

        $requiredKeys = ['client_id', 'client_secret', 'redirect_uri', 'region'];

        foreach($requiredKeys as $requiredKey) {
            if (!isset($_ENV[$requiredKey])) {
                throw new Exception($requiredKey . ' needs to be specified in .env');
            }
        }
    }

    /**
     * Create the DI container
     * @return Container
     */
    public static function getContainer()
    {
        // construct DI container
        $container = new Container();

        $container['logger'] = function () {
            // Create the logger
            $logger = new Logger('my_logger');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(
                __DIR__ . '/../log/' . date('Y-m-d') . '.log',
                Logger::DEBUG)
            );
            return $logger;
        };

        $container['guzzle'] = function() {
            return new Client([
                'base_uri' => 'https://' . filter_var($_ENV['region'], FILTER_SANITIZE_STRING) . '.api.blizzard.com',
                'timeout'  => 2.0,
            ]);
        };

        $container['oauth2Provider'] = function($container) {
            $options = [
                'clientId' => $_ENV['client_id'],
                'clientSecret' => $_ENV['client_secret'],
                'redirectUri' => $_ENV['redirect_uri'],
                'region' => $ENV['region'] ?? 'eu'
            ];
            $container['logger']->debug("Options", $options);
            return new WowProvider($options);
        };

        return $container;
    }
}