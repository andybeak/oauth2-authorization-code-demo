# Readme

This project is an example of using the Battle Net provider for the OAuth 2.0 Client written by The League of Extraordinary Packages.

See:
* https://github.com/thephpleague/oauth2-client
* https://github.com/tpavlek/oauth2-bnet

## Getting a client key

Register a new client at https://develop.battle.net/access/clients

## Running the demo

Install the dependencies with `composer install`

Copy `.env.example` to `.env` and add your client id and secret.

Run the application with `php -S localhost:8000`.

Visit `http://localhost:8000` in your browser.