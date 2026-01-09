<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class WebhookController extends Controller
{
    /**
     * Handle GitHub webhook for automatic deployment
     */
    public function github(Request $request)
    {
        // Get the signature from the request
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        
        // Verify webhook secret (optional but recommended)
        $secret = config('app.webhook_secret', env('WEBHOOK_SECRET'));
        
        if ($secret) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Webhook signature verification failed', [
                    'expected' => $expectedSignature,
                    'received' => $signature
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 403);
            }
        }
        
        // Parse the payload
        $data = json_decode($payload, true);
        
        // Only process push events to main/master branch
        $ref = $data['ref'] ?? '';
        $branch = str_replace('refs/heads/', '', $ref);
        
        if ($data['zen'] ?? false) {
            // GitHub ping event
            return response()->json([
                'success' => true,
                'message' => 'Webhook is working!'
            ]);
        }
        
        if (($data['action'] ?? '') !== 'push' && !isset($data['commits'])) {
            return response()->json([
                'success' => false,
                'message' => 'Not a push event'
            ], 200);
        }
        
        // Only deploy from main/master branch
        if (!in_array($branch, ['main', 'master'])) {
            Log::info('Webhook received for non-main branch', ['branch' => $branch]);
            return response()->json([
                'success' => false,
                'message' => 'Branch ' . $branch . ' is not configured for auto-deployment'
            ], 200);
        }
        
        try {
            Log::info('Webhook triggered deployment', [
                'branch' => $branch,
                'commits' => count($data['commits'] ?? [])
            ]);
            
            // Execute deployment script
            $deploymentResult = $this->deploy();
            
            return response()->json([
                'success' => true,
                'message' => 'Deployment triggered successfully',
                'branch' => $branch,
                'deployment' => $deploymentResult
            ]);
            
        } catch (\Exception $e) {
            Log::error('Webhook deployment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Deployment failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Execute deployment
     */
    protected function deploy()
    {
        $basePath = base_path();
        $results = [];
        
        // Change to project directory
        chdir($basePath);
        
        // 1. Fetch latest changes
        $process = new Process(['git', 'fetch', 'origin'], $basePath);
        $process->setTimeout(300);
        $process->run();
        $results['fetch'] = [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput()
        ];
        
        if (!$process->isSuccessful()) {
            throw new \Exception('Git fetch failed: ' . $process->getErrorOutput());
        }
        
        // 2. Merge changes
        $process = new Process(['git', 'merge', '--no-ff', 'origin/main', '-m', 'Auto-deploy: ' . date('Y-m-d H:i:s')], $basePath);
        $process->setTimeout(300);
        $process->run();
        $results['merge'] = [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput()
        ];
        
        if (!$process->isSuccessful()) {
            // Check if it's a merge conflict or just already up to date
            if (strpos($process->getErrorOutput(), 'Already up to date') !== false) {
                $results['merge']['note'] = 'Already up to date';
            } else {
                throw new \Exception('Git merge failed: ' . $process->getErrorOutput());
            }
        }
        
        // 3. Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $results['migrate'] = [
                'success' => true,
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            $results['migrate'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            // Don't fail deployment if migration fails
        }
        
        // 4. Clear caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $results['cache_clear'] = ['success' => true];
        
        // 5. Optimize
        try {
            Artisan::call('optimize');
            $results['optimize'] = ['success' => true];
        } catch (\Exception $e) {
            $results['optimize'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return $results;
    }
    
    /**
     * Test webhook endpoint (for testing without GitHub)
     */
    public function test()
    {
        try {
            $result = $this->deploy();
            
            return response()->json([
                'success' => true,
                'message' => 'Test deployment completed',
                'results' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test deployment failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

