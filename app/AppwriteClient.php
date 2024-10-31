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
     * @throws \Exception if Appwrite Client is not available
     */
    public static function getClient(
        string $endpoint = null, 
        string $project = null, 
        string $secret = null
    ): Client {
        // Ensure the Appwrite\Client class is available
        if (!class_exists(Client::class)) {
            throw new \Exception("Appwrite Client class is required but not found. Please ensure the Appwrite SDK is installed.");
        }

        // Initialize the client only if it has not been created yet
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
    
    /**
     * Get an instance of the specified Appwrite service.
     *
     * @param string $serviceName
     * @return mixed
     * @throws \Exception if the service class does not exist
     */
    public static function getService(
        string $serviceName
    ) {
        $serviceClass = "Appwrite\\Services\\$serviceName";

        // Ensure the specified service class is available
        if (!class_exists($serviceClass)) {
            throw new \Exception("Appwrite service class '$serviceClass' does not exist. Please check the service name.");
        }

        return new $serviceClass(self::getClient());
    }
}
