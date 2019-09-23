<?php

require('vendor/autoload.php');

use Demo\App;
use Demo\Bootstrap;

try {
    session_start();

    Bootstrap::loadConfig();
    $container = Bootstrap::getContainer();

    $app = new App();
    $app->setProvider($container['oauth2Provider']);
    $app->setLogger($container['logger']);
    $app->setGuzzleClient($container['guzzle']);

    // If we don't have an authorization code then get one
    if (!isset($_GET['code'])) {

        $app->requestAuthorization();

    } else {

        // Check given state against previously stored one to mitigate CSRF attack
        $app->checkState();

        $app->getAccessToken($_GET['code']);

    }

    // at this point we should have an access token and can make an API call
    $characterDetails = $app->getCharacterDetails();

    $renderDomain = 'http://render-' . filter_var($_ENV['region'], FILTER_SANITIZE_STRING) . '.worldofwarcraft.com';

    echo "<img src='$renderDomain/character/{$characterDetails['thumbnail']}'>";

} catch (Throwable $e) {

    if (isset($container['logger'])) {

        $container['logger']->error($e->getMessage());

    }

    echo "An error occurred";

}

