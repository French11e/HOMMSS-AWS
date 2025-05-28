<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SecurityTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:test 
                            {action : enable, disable, status, or demo}
                            {--type= : Specific vulnerability type (xss, clickjacking, sql)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage security testing mode and vulnerability simulations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $type = $this->option('type');

        switch ($action) {
            case 'enable':
                return $this->enableTesting($type);
            case 'disable':
                return $this->disableTesting($type);
            case 'status':
                return $this->showStatus();
            case 'demo':
                return $this->runDemo();
            default:
                $this->error('Invalid action. Use: enable, disable, status, or demo');
                return Command::FAILURE;
        }
    }

    /**
     * Enable security testing
     */
    protected function enableTesting($type = null)
    {
        $this->info('HOMMSS Security Testing');
        $this->info('======================');

        if ($type) {
            return $this->enableSpecificTest($type);
        }

        $this->warn('WARNING: This will enable security vulnerability simulations!');
        $this->warn('Only use this in a testing environment, never in production!');

        if (!$this->confirm('Do you want to enable security testing mode?')) {
            $this->info('Security testing mode not enabled.');
            return Command::SUCCESS;
        }

        // Enable general testing mode
        $this->updateEnvValue('SECURITY_TESTING_MODE', 'true');
        
        $this->info('Security testing mode enabled.');
        $this->info('Access the dashboard at: /security/dashboard');
        $this->info('');
        $this->info('Available vulnerability tests:');
        $this->info('- XSS (Cross-Site Scripting)');
        $this->info('- Clickjacking');
        $this->info('- SQL Injection');
        $this->info('');
        $this->warn('Remember to disable testing mode when finished!');

        return Command::SUCCESS;
    }

    /**
     * Enable specific vulnerability test
     */
    protected function enableSpecificTest($type)
    {
        $this->updateEnvValue('SECURITY_TESTING_MODE', 'true');

        switch ($type) {
            case 'xss':
                $this->updateEnvValue('XSS_SIMULATION_ENABLED', 'true');
                $this->updateEnvValue('XSS_REFLECTED_ENABLED', 'true');
                $this->updateEnvValue('XSS_STORED_ENABLED', 'true');
                $this->updateEnvValue('XSS_DOM_ENABLED', 'true');
                $this->warn('XSS vulnerability simulation enabled!');
                break;

            case 'clickjacking':
                $this->updateEnvValue('CLICKJACKING_SIMULATION_ENABLED', 'true');
                $this->updateEnvValue('CLICKJACKING_REMOVE_HEADERS', 'true');
                $this->warn('Clickjacking vulnerability simulation enabled!');
                break;

            case 'sql':
                $this->updateEnvValue('SQL_INJECTION_SIMULATION_ENABLED', 'true');
                $this->updateEnvValue('SQL_INJECTION_RAW_QUERIES', 'true');
                $this->warn('SQL Injection vulnerability simulation enabled!');
                break;

            default:
                $this->error('Invalid type. Use: xss, clickjacking, or sql');
                return Command::FAILURE;
        }

        $this->info("Access the dashboard at: /security/dashboard");
        return Command::SUCCESS;
    }

    /**
     * Disable security testing
     */
    protected function disableTesting($type = null)
    {
        if ($type) {
            return $this->disableSpecificTest($type);
        }

        // Disable all testing
        $this->updateEnvValue('SECURITY_TESTING_MODE', 'false');
        $this->updateEnvValue('XSS_SIMULATION_ENABLED', 'false');
        $this->updateEnvValue('XSS_REFLECTED_ENABLED', 'false');
        $this->updateEnvValue('XSS_STORED_ENABLED', 'false');
        $this->updateEnvValue('XSS_DOM_ENABLED', 'false');
        $this->updateEnvValue('CLICKJACKING_SIMULATION_ENABLED', 'false');
        $this->updateEnvValue('CLICKJACKING_REMOVE_HEADERS', 'false');
        $this->updateEnvValue('SQL_INJECTION_SIMULATION_ENABLED', 'false');
        $this->updateEnvValue('SQL_INJECTION_RAW_QUERIES', 'false');

        $this->info('All security testing disabled.');
        $this->info('Application is now in secure mode.');

        return Command::SUCCESS;
    }

    /**
     * Disable specific vulnerability test
     */
    protected function disableSpecificTest($type)
    {
        switch ($type) {
            case 'xss':
                $this->updateEnvValue('XSS_SIMULATION_ENABLED', 'false');
                $this->updateEnvValue('XSS_REFLECTED_ENABLED', 'false');
                $this->updateEnvValue('XSS_STORED_ENABLED', 'false');
                $this->updateEnvValue('XSS_DOM_ENABLED', 'false');
                $this->info('XSS vulnerability simulation disabled.');
                break;

            case 'clickjacking':
                $this->updateEnvValue('CLICKJACKING_SIMULATION_ENABLED', 'false');
                $this->updateEnvValue('CLICKJACKING_REMOVE_HEADERS', 'false');
                $this->info('Clickjacking vulnerability simulation disabled.');
                break;

            case 'sql':
                $this->updateEnvValue('SQL_INJECTION_SIMULATION_ENABLED', 'false');
                $this->updateEnvValue('SQL_INJECTION_RAW_QUERIES', 'false');
                $this->info('SQL Injection vulnerability simulation disabled.');
                break;

            default:
                $this->error('Invalid type. Use: xss, clickjacking, or sql');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Show current security testing status
     */
    protected function showStatus()
    {
        $this->info('HOMMSS Security Testing Status');
        $this->info('==============================');

        $testingMode = env('SECURITY_TESTING_MODE', false);
        $this->info('Testing Mode: ' . ($testingMode ? 'ENABLED' : 'DISABLED'));

        if ($testingMode) {
            $this->warn('Security testing is currently active!');
            $this->info('');
            $this->info('Vulnerability Simulations:');
            
            // XSS Status
            $xssEnabled = env('XSS_SIMULATION_ENABLED', false);
            $this->info('XSS Testing: ' . ($xssEnabled ? 'ENABLED' : 'DISABLED'));
            if ($xssEnabled) {
                $this->info('  - Reflected XSS: ' . (env('XSS_REFLECTED_ENABLED', false) ? 'VULNERABLE' : 'PROTECTED'));
                $this->info('  - Stored XSS: ' . (env('XSS_STORED_ENABLED', false) ? 'VULNERABLE' : 'PROTECTED'));
                $this->info('  - DOM XSS: ' . (env('XSS_DOM_ENABLED', false) ? 'VULNERABLE' : 'PROTECTED'));
            }

            // Clickjacking Status
            $clickjackingEnabled = env('CLICKJACKING_SIMULATION_ENABLED', false);
            $this->info('Clickjacking Testing: ' . ($clickjackingEnabled ? 'ENABLED' : 'DISABLED'));
            if ($clickjackingEnabled) {
                $this->info('  - Frame Protection: ' . (env('CLICKJACKING_REMOVE_HEADERS', false) ? 'VULNERABLE' : 'PROTECTED'));
            }

            // SQL Injection Status
            $sqlEnabled = env('SQL_INJECTION_SIMULATION_ENABLED', false);
            $this->info('SQL Injection Testing: ' . ($sqlEnabled ? 'ENABLED' : 'DISABLED'));
            if ($sqlEnabled) {
                $this->info('  - Raw Queries: ' . (env('SQL_INJECTION_RAW_QUERIES', false) ? 'VULNERABLE' : 'PROTECTED'));
            }

            $this->info('');
            $this->info('Dashboard URL: /security/dashboard');
        } else {
            $this->info('Application is in secure mode.');
        }

        return Command::SUCCESS;
    }

    /**
     * Run security demonstration
     */
    protected function runDemo()
    {
        $this->info('HOMMSS Security Demonstration');
        $this->info('=============================');
        $this->info('');
        $this->info('This demonstration shows how security vulnerabilities work');
        $this->info('and how our application protects against them.');
        $this->info('');

        $this->info('Available demonstrations:');
        $this->info('1. XSS Protection Demo');
        $this->info('2. Clickjacking Protection Demo');
        $this->info('3. SQL Injection Protection Demo');
        $this->info('4. Complete Security Report');
        $this->info('');

        $choice = $this->choice('Select demonstration', [
            'XSS Protection',
            'Clickjacking Protection', 
            'SQL Injection Protection',
            'Complete Security Report',
            'Exit'
        ]);

        switch ($choice) {
            case 'XSS Protection':
                $this->demoXSS();
                break;
            case 'Clickjacking Protection':
                $this->demoClickjacking();
                break;
            case 'SQL Injection Protection':
                $this->demoSQL();
                break;
            case 'Complete Security Report':
                $this->demoReport();
                break;
            case 'Exit':
                $this->info('Demonstration ended.');
                break;
        }

        return Command::SUCCESS;
    }

    /**
     * Demo XSS protection
     */
    protected function demoXSS()
    {
        $this->info('XSS (Cross-Site Scripting) Protection Demo');
        $this->info('==========================================');
        $this->info('XSS attacks inject malicious scripts into web pages.');
        $this->info('Our protection: htmlspecialchars() and Content Security Policy');
        $this->info('Test at: /security/dashboard');
    }

    /**
     * Demo Clickjacking protection
     */
    protected function demoClickjacking()
    {
        $this->info('Clickjacking Protection Demo');
        $this->info('===========================');
        $this->info('Clickjacking tricks users into clicking hidden elements.');
        $this->info('Our protection: X-Frame-Options and CSP frame-ancestors');
        $this->info('Test at: /security/dashboard');
    }

    /**
     * Demo SQL Injection protection
     */
    protected function demoSQL()
    {
        $this->info('SQL Injection Protection Demo');
        $this->info('============================');
        $this->info('SQL injection attacks manipulate database queries.');
        $this->info('Our protection: Prepared statements and input validation');
        $this->info('Test at: /security/dashboard');
    }

    /**
     * Demo security report
     */
    protected function demoReport()
    {
        $this->info('Complete Security Report');
        $this->info('========================');
        $this->info('Visit /security/report for a complete security analysis');
        $this->info('Or use the dashboard at /security/dashboard');
    }

    /**
     * Update environment variable
     */
    protected function updateEnvValue($key, $value)
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            $envContent .= "\n{$replacement}";
        }

        File::put($envFile, $envContent);
    }
}
