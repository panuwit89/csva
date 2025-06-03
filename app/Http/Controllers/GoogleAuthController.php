<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;
use Google\Service\Oauth2 as GoogleOauth2;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    private $googleClient;

    public function __construct()
    {
        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(config('services.google.client_secret'));
        $this->googleClient->setRedirectUri(config('services.google.redirect'));

        // Set scopes
        $this->googleClient->addScope([
            'email',
            'profile',
            'openid',
        ]);

        // Enable offline access to get refresh token
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setApprovalPrompt('force');

        // Set hosted domain restriction
        $this->googleClient->setHostedDomain('ku.th');
    }

    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirect()
    {
        try {
            // Generate and store state for CSRF protection
            $state = Str::random(32);
            session(['google_oauth_state' => $state]);
            $this->googleClient->setState($state);

            // Create auth URL
            $authUrl = $this->googleClient->createAuthUrl();

            return redirect($authUrl);
        } catch (Throwable $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Unable to connect to Google: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request)
    {
        try {
            // Verify state parameter for CSRF protection
            if (!$request->has('state') || $request->state !== session('google_oauth_state')) {
                Log::error('Google OAuth state mismatch');
                return redirect('/login')->with('error', 'Security verification failed.');
            }

            // Check for authorization code
            if (!$request->has('code')) {
                Log::error('Google callback missing code parameter');
                return redirect('/login')->with('error', 'Google authentication was cancelled or failed.');
            }

            // Exchange authorization code for access token
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($request->code);

            if (isset($token['error'])) {
                Log::error('Google token error: ' . $token['error']);
                return redirect('/login')->with('error', 'Failed to obtain access token from Google.');
            }

            // Set the access token
            $this->googleClient->setAccessToken($token);

            // Get user information
            $oauth2Service = new GoogleOauth2($this->googleClient);
            $googleUser = $oauth2Service->userinfo->get();

            if (!$googleUser || !$googleUser->email) {
                Log::error('Failed to get user info from Google');
                return redirect('/login')->with('error', 'Failed to get user information from Google.');
            }

            Log::info('Google user retrieved: ' . $googleUser->email);

            // Check if user exists by email
            $user = User::where('email', $googleUser->email)->first();

            $userData = [
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->picture ?? null,
                'google_access_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? null,
                'google_token_expires_at' => isset($token['expires_in'])
                    ? now()->addSeconds($token['expires_in'])
                    : now()->addHour(),
            ];

            if ($user) {
                // Update existing user
                $user->update($userData);
                Auth::login($user);
                Log::info('Existing user logged in: ' . $user->email);
            } else {
                // Create new user
                $userData = array_merge($userData, [
                    'name' => $googleUser->name ?? 'Google User',
                    'email' => $googleUser->email,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]);

                $user = User::create($userData);
                Auth::login($user);
                Log::info('New user created and logged in: ' . $user->email);
            }

            // Clear the state from session
            session()->forget('google_oauth_state');

            return redirect('/chat')->with('success', 'Successfully logged in with Google!');

        } catch (Throwable $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }
    }

    /**
     * Refresh Google access token
     */
    public function refreshToken(User $user): ?string
    {
        try {
            if (!$user->google_refresh_token) {
                return null;
            }

            $this->googleClient->refreshToken($user->google_refresh_token);
            $newToken = $this->googleClient->getAccessToken();

            if ($newToken) {
                $user->update([
                    'google_access_token' => $newToken['access_token'],
                    'google_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);

                return $newToken['access_token'];
            }

            return null;
        } catch (Throwable $e) {
            Log::error('Failed to refresh Google token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a valid Google access token for the user
     */
    public function getValidAccessToken(User $user): ?string
    {
        // Check if current token is still valid (with 5-minute buffer)
        if ($user->google_access_token && $user->google_token_expires_at > now()->addMinutes(3)) {
            return $user->google_access_token;
        }

        // Try to refresh the token
        return $this->refreshToken($user);
    }

    /**
     * Revoke user's Google access
     */
    public function revoke(User $user): bool
    {
        try {
            if ($user->google_access_token) {
                $this->googleClient->revokeToken($user->google_access_token);
            }

            $user->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to revoke Google token: ' . $e->getMessage());
            return false;
        }
    }
}
