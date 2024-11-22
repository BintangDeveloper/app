<?php

namespace App\Http\Controllers;

use App\Models\GoogleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;


class GoogleOAuthController extends Controller
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct()
    {
        $this->client_id = config('services.google.client_id');
        $this->client_secret = config('services.google.client_secret');
        $this->redirect_uri = config('services.google.redirect_uri');
    }

    public function redirectToGoogle()
    {
        $google_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth';

        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        $query = http_build_query($params);

        return redirect("{$google_oauth_url}?{$query}");
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        // Exchange the authorization code for an access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Token exchange failed'], 400);
        }

        $data = $response->json();
        $access_token = $data['access_token'];
        $refresh_token = $data['refresh_token'] ?? null;

        // Retrieve user profile
        $user_info = Http::withToken($access_token)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (!$user_info->successful()) {
            return response()->json(['error' => 'Failed to fetch user info'], 400);
        }

        $google_user = $user_info->json();

        // Check if user exists or create a new one
        $user = GoogleUser::updateOrCreate(
            ['google_id' => $google_user['id']],
            [
                'name' => $google_user['name'],
                'email' => $google_user['email'],
                'avatar' => $google_user['picture'],
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
            ]
        );

        // Generate a Passport token for the user
        $token = $user->createToken('Personal Access Token')->accessToken;
        
        Cookie::queue(Cookie::make('security', $token, 4320));

        return response()->json([
            'message' => 'Successfully authenticated',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
