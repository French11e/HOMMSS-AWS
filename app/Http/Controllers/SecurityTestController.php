<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class SecurityTestController extends Controller
{
    /**
     * Security Testing Dashboard
     */
    public function index()
    {
        // Only allow in testing mode
        if (!env('SECURITY_TESTING_MODE', false)) {
            abort(404);
        }

        return view('security.testing-dashboard');
    }

    /**
     * XSS Attack Simulation
     */
    public function xssTest(Request $request)
    {
        if (!env('XSS_SIMULATION_ENABLED', false)) {
            return response()->json(['error' => 'XSS simulation disabled'], 403);
        }

        $type = $request->get('type', 'reflected');
        $payload = $request->get('payload', env('XSS_TEST_PAYLOAD'));

        Log::warning('XSS Attack Simulation', [
            'type' => $type,
            'payload' => $payload,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        switch ($type) {
            case 'reflected':
                return $this->reflectedXSS($payload);
            case 'stored':
                return $this->storedXSS($payload);
            case 'dom':
                return $this->domXSS($payload);
            default:
                return response()->json(['error' => 'Invalid XSS type'], 400);
        }
    }

    /**
     * Reflected XSS Simulation
     */
    protected function reflectedXSS($payload)
    {
        if (!env('XSS_REFLECTED_ENABLED', false)) {
            // Secure version - escape output
            $safePayload = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
            return response()->json([
                'status' => 'PROTECTED',
                'message' => 'XSS attempt blocked',
                'safe_output' => $safePayload,
                'protection' => 'htmlspecialchars() applied'
            ]);
        }

        // Vulnerable version - direct output (ONLY FOR TESTING)
        return response()->json([
            'status' => 'VULNERABLE',
            'message' => 'XSS simulation active',
            'raw_output' => $payload,
            'warning' => 'This would execute in a real attack'
        ]);
    }

    /**
     * Stored XSS Simulation
     */
    protected function storedXSS($payload)
    {
        if (!env('XSS_STORED_ENABLED', false)) {
            // Secure version - sanitize before storage
            $safePayload = strip_tags($payload);
            return response()->json([
                'status' => 'PROTECTED',
                'message' => 'Stored XSS attempt blocked',
                'sanitized' => $safePayload,
                'protection' => 'strip_tags() applied'
            ]);
        }

        // Vulnerable version - store raw payload (ONLY FOR TESTING)
        return response()->json([
            'status' => 'VULNERABLE',
            'message' => 'Stored XSS simulation - payload would be stored',
            'stored_payload' => $payload,
            'warning' => 'This would persist and execute for other users'
        ]);
    }

    /**
     * DOM XSS Simulation
     */
    protected function domXSS($payload)
    {
        if (!env('XSS_DOM_ENABLED', false)) {
            return response()->json([
                'status' => 'PROTECTED',
                'message' => 'DOM XSS protection active',
                'protection' => 'Content Security Policy headers'
            ]);
        }

        return response()->json([
            'status' => 'VULNERABLE',
            'message' => 'DOM XSS simulation',
            'payload' => $payload,
            'warning' => 'This would execute in the DOM'
        ]);
    }

    /**
     * Clickjacking Attack Simulation
     */
    public function clickjackingTest(Request $request)
    {
        if (!env('CLICKJACKING_SIMULATION_ENABLED', false)) {
            return response()->json(['error' => 'Clickjacking simulation disabled'], 403);
        }

        Log::warning('Clickjacking Attack Simulation', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if (env('CLICKJACKING_REMOVE_HEADERS', false)) {
            // Vulnerable version - no frame protection
            return response()->view('security.clickjacking-vulnerable')
                ->header('X-Frame-Options', 'ALLOWALL');
        }

        // Secure version - frame protection enabled
        return response()->json([
            'status' => 'PROTECTED',
            'message' => 'Clickjacking protection active',
            'headers' => [
                'X-Frame-Options' => 'DENY',
                'Content-Security-Policy' => "frame-ancestors 'none'"
            ]
        ])->header('X-Frame-Options', 'DENY')
          ->header('Content-Security-Policy', "frame-ancestors 'none'");
    }

    /**
     * SQL Injection Attack Simulation
     */
    public function sqlInjectionTest(Request $request)
    {
        if (!env('SQL_INJECTION_SIMULATION_ENABLED', false)) {
            return response()->json(['error' => 'SQL injection simulation disabled'], 403);
        }

        $input = $request->get('input', "1' OR '1'='1");
        
        Log::warning('SQL Injection Attack Simulation', [
            'input' => $input,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if (env('SQL_INJECTION_RAW_QUERIES', false)) {
            // Vulnerable version - raw query (ONLY FOR TESTING)
            try {
                $query = "SELECT * FROM users WHERE id = '{$input}'";
                return response()->json([
                    'status' => 'VULNERABLE',
                    'message' => 'SQL injection simulation',
                    'query' => $query,
                    'warning' => 'This would execute malicious SQL'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'SQL injection attempt failed',
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Secure version - prepared statements
        try {
            $users = DB::table('users')->where('id', $input)->get();
            return response()->json([
                'status' => 'PROTECTED',
                'message' => 'SQL injection attempt blocked',
                'protection' => 'Prepared statements used',
                'safe_query' => 'SELECT * FROM users WHERE id = ?',
                'parameter' => $input
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'PROTECTED',
                'message' => 'Invalid input rejected',
                'error' => 'Parameter validation failed'
            ]);
        }
    }

    /**
     * Security Test Report
     */
    public function securityReport()
    {
        if (!env('SECURITY_TESTING_MODE', false)) {
            abort(404);
        }

        $protections = [
            'xss' => [
                'reflected' => !env('XSS_REFLECTED_ENABLED', false),
                'stored' => !env('XSS_STORED_ENABLED', false),
                'dom' => !env('XSS_DOM_ENABLED', false),
            ],
            'clickjacking' => !env('CLICKJACKING_REMOVE_HEADERS', false),
            'sql_injection' => !env('SQL_INJECTION_RAW_QUERIES', false),
        ];

        $vulnerabilities = [];
        if (env('XSS_REFLECTED_ENABLED', false)) $vulnerabilities[] = 'Reflected XSS';
        if (env('XSS_STORED_ENABLED', false)) $vulnerabilities[] = 'Stored XSS';
        if (env('XSS_DOM_ENABLED', false)) $vulnerabilities[] = 'DOM XSS';
        if (env('CLICKJACKING_REMOVE_HEADERS', false)) $vulnerabilities[] = 'Clickjacking';
        if (env('SQL_INJECTION_RAW_QUERIES', false)) $vulnerabilities[] = 'SQL Injection';

        return response()->json([
            'security_status' => empty($vulnerabilities) ? 'SECURE' : 'VULNERABLE',
            'protections' => $protections,
            'active_vulnerabilities' => $vulnerabilities,
            'recommendations' => $this->getSecurityRecommendations()
        ]);
    }

    /**
     * Get security recommendations
     */
    protected function getSecurityRecommendations()
    {
        return [
            'xss_protection' => [
                'Use htmlspecialchars() for output',
                'Implement Content Security Policy',
                'Validate and sanitize all inputs',
                'Use Laravel\'s Blade templating (auto-escaping)'
            ],
            'clickjacking_protection' => [
                'Set X-Frame-Options header',
                'Use Content-Security-Policy frame-ancestors',
                'Implement CSRF protection',
                'Validate referrer headers'
            ],
            'sql_injection_protection' => [
                'Use prepared statements (Laravel Eloquent)',
                'Validate all inputs',
                'Use parameterized queries',
                'Implement input sanitization'
            ]
        ];
    }
}
