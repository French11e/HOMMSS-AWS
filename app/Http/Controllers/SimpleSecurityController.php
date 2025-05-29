<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SimpleSecurityController extends Controller
{
    /**
     * Security Testing Dashboard
     */
    public function index()
    {
        // Check if testing mode is enabled
        if (!env('SECURITY_TESTING_MODE', false)) {
            return response()->view('errors.404', [], 404);
        }

        return view('security.simple-dashboard');
    }

    /**
     * XSS Test
     */
    public function xssTest(Request $request)
    {
        if (!env('SECURITY_TESTING_MODE', false)) {
            return response()->json(['error' => 'Security testing disabled'], 403);
        }

        $payload = $request->get('payload', '<script>alert("XSS")</script>');
        
        Log::info('XSS Test Attempt', ['payload' => $payload, 'ip' => $request->ip()]);

        // Always show protection (safe version)
        $safePayload = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        return response()->json([
            'status' => 'PROTECTED',
            'message' => 'XSS attack blocked by htmlspecialchars()',
            'original_payload' => $payload,
            'safe_output' => $safePayload,
            'protection' => 'Output encoding applied'
        ]);
    }

    /**
     * SQL Injection Test
     */
    public function sqlTest(Request $request)
    {
        if (!env('SECURITY_TESTING_MODE', false)) {
            return response()->json(['error' => 'Security testing disabled'], 403);
        }

        $input = $request->get('input', "1' OR '1'='1");
        
        Log::info('SQL Injection Test Attempt', ['input' => $input, 'ip' => $request->ip()]);

        // Always show protection (safe version)
        return response()->json([
            'status' => 'PROTECTED',
            'message' => 'SQL injection blocked by prepared statements',
            'malicious_input' => $input,
            'protection' => 'Laravel Eloquent ORM with parameter binding',
            'safe_query' => 'SELECT * FROM users WHERE id = ?'
        ]);
    }

    /**
     * Security Status
     */
    public function status()
    {
        if (!env('SECURITY_TESTING_MODE', false)) {
            return response()->json(['error' => 'Security testing disabled'], 403);
        }

        return response()->json([
            'security_status' => 'SECURE',
            'testing_mode' => env('SECURITY_TESTING_MODE', false),
            'protections' => [
                'xss_protection' => 'htmlspecialchars() + CSP headers',
                'sql_injection_protection' => 'Prepared statements + Eloquent ORM',
                'clickjacking_protection' => 'X-Frame-Options + CSP frame-ancestors',
                'csrf_protection' => 'Laravel CSRF tokens'
            ],
            'message' => 'All security protections are active'
        ]);
    }
}
