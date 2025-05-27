<?php

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurnstileRule implements ValidationRule
{
    private TurnstileService $turnstileService;

    public function __construct()
    {
        $this->turnstileService = app(TurnstileService::class);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if Turnstile is not enabled
        if (!$this->turnstileService->isEnabled()) {
            return;
        }

        // Get the client IP
        $remoteIp = request()->ip();

        // Verify the token
        if (!$this->turnstileService->verify($value, $remoteIp)) {
            $fail('Please complete the security verification.');
        }
    }
}
