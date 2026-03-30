<?php

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Http;

class GoogleTokenVerifier
{
    /**
     * Verify Google ID token and return normalized profile.
     */
    public function verifyIdToken(string $idToken): array
    {
        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->successful()) {
            throw new UnauthorizedException('Invalid Google token.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new UnauthorizedException('Invalid Google token payload.');
        }

        $issuer = $payload['iss'] ?? null;
        if (! in_array($issuer, ['https://accounts.google.com', 'accounts.google.com'], true)) {
            throw new UnauthorizedException('Invalid Google token issuer.');
        }

        $audience = $payload['aud'] ?? null;
        $allowedAudiences = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.google.client_id', ''))
        ));

        if (! empty($allowedAudiences) && ! in_array($audience, $allowedAudiences, true)) {
            throw new UnauthorizedException('Google token audience mismatch.');
        }

        $expiresAt = (int) ($payload['exp'] ?? 0);
        if ($expiresAt > 0 && $expiresAt < time()) {
            throw new UnauthorizedException('Google token has expired.');
        }

        $googleId = $payload['sub'] ?? null;
        $email = isset($payload['email']) ? strtolower((string) $payload['email']) : null;
        if (! $googleId || ! $email) {
            throw new UnauthorizedException('Google token missing required profile information.');
        }

        return [
            'google_id' => (string) $googleId,
            'email' => $email,
            'name' => (string) ($payload['name'] ?? strstr($email, '@', true) ?: 'Google User'),
            'avatar' => isset($payload['picture']) ? (string) $payload['picture'] : null,
            'email_verified' => filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }
}
