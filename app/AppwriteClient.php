<?php

namespace App;

use Appwrite\Client;
use Appwrite\Service;

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
     * @throws \Exception if Appwrite Client is not available
     */
    public static function getClient(
        string $endpoint = null, 
        string $project = null, 
        string $secret = null,
        bool $selfSigned = false 
    ): Client {
      
        if (!class_exists(Client::class)) {
            throw new \Exception("Appwrite Client class is required but not found. Please ensure the Appwrite SDK is installed.");
        }

        if (is_null(self::$client)) {
            self::$client = new Client();
            self::$client
                ->setEndpoint($endpoint ?? env('APPWRITE_ENDPOINT', 'https://cloud.appwrite.io/v1'))
                ->setProject($project ?? env('APPWRITE_PROJECTID', 'XXXXXXXXXXXXXXXXXXX'))
                ->setKey($secret ?? env('APPWRITE_SECRET', 'standard_XXXXXXXXXX'));

            if (env('APPWRITE_SELF_SIGNED', false) || $selfSigned) {
                self::$client->setSelfSigned();
            }
        }

        return self::$client;
    }
    
    /**
     * Get an instance of the specified Appwrite service.
     *
     * @param string $serviceName
     * @return Service
     * @throws \Exception if the service class does not exist
     */
    public static function getService(
      string $serviceName
    ): Service {
      
        $serviceClass = "Appwrite\\Services\\$serviceName";

        if (!class_exists($serviceClass)) {
            throw new \Exception("Appwrite service class '$serviceClass' does not exist. Please check the service name.");
        }

        return new $serviceClass(self::getClient());
    }
}
