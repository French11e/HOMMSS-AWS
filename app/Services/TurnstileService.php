<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    private string $secretKey;
    private string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        $this->secretKey = config('services.turnstile.secret_key');
    }

    /**
     * Verify Turnstile token
     */
    public function verify(string $token, string $remoteIp = null): bool
    {
        if (empty($this->secretKey)) {
            Log::warning('Turnstile secret key not configured');
            return false;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            if (!$response->successful()) {
                Log::error('Turnstile verification request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $data = $response->json();

            if (!isset($data['success'])) {
                Log::error('Invalid Turnstile response format', ['response' => $data]);
                return false;
            }

            if (!$data['success']) {
                Log::warning('Turnstile verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'ip' => $remoteIp
                ]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Turnstile verification exception', [
                'message' => $e->getMessage(),
                'ip' => $remoteIp
            ]);
            return false;
        }
    }

    /**
     * Get the site key for frontend
     */
    public function getSiteKey(): string
    {
        return config('services.turnstile.site_key', '');
    }

    /**
     * Check if Turnstile is enabled
     */
    public function isEnabled(): bool
    {
        return !empty($this->secretKey) && !empty($this->getSiteKey());
    }
}
