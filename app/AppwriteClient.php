<?php

namespace App;

use Appwrite\Client;

class AppwriteClient
{
    /**
     * The Appwrite client instance.
     *
     * @var Client|null
     */
    private static ?Client $client = null;

    /**
     * Get the Appwrite client instance, initializing if necessary.
     *
     * @param string|null $endpoint
     * @param string|null $project
     * @param string|null $secret
     * @return Client
     */
    public static function getClient(
      string $endpoint = null, 
      string $project = null, 
      string $secret = null
    ): Client {
      
        if (is_null(self::$client)) {
            self::$client = new Client();
            self::$client
                ->setEndpoint($endpoint ?? env('APPWRITE_ENDPOINT'))
                ->setProject($project ?? env('APPWRITE_PROJECTID'))
                ->setKey($secret ?? env('APPWRITE_SECRET'))
                ->setSelfSigned();
        }

        return self::$client;
    }
}
