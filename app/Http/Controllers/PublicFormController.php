<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Services\CloudflareR2Service;
use App\Services\LocalStorageService;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{

    public function submit(Request $request, $token)
    {
        \Log::info('UAT Form submission attempt', [
            'token' => $token,
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'origin' => $request->header('Origin'),
        ]);

        $form = Form::where('embed_token', $token)->first();

        if (!$form) {
            \Log::error('UAT Form not found', ['token' => $token]);
            return response()->json(['error' => 'Invalid form token'], 404)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // Check if form is active
        if (!$form->is_active) {
            \Log::error('UAT Form is not active', ['token' => $token]);
            return response()->json(['error' => 'Form is not active'], 403)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // Check domain validation
        $origin = $request->header('Origin');
        if ($origin) {
            $parsedUrl = parse_url($origin);
            $domain = $parsedUrl['host'] ?? '';
            
            if (!$form->isDomainAllowed($domain)) {
                \Log::error('UAT Form submission from unauthorized domain', [
                    'token' => $token,
                    'domain' => $domain,
                    'allowed_domains' => $form->allowed_domains,
                ]);
                return response()->json(['error' => 'Domain not authorized'], 403)
                    ->header('Access-Control-Allow-Origin', '*');
            }
        }

        try {
            $validated = $request->validate([
            'issue_description' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high',
            'submitter_name' => 'required|string|max:255',
            'submitter_email' => 'required|email|max:255',
            'page_url' => 'required|string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|array',
            'attachments.*.data' => 'required_with:attachments.*|string',
            'attachments.*.name' => 'required_with:attachments.*|string',
            // New metadata fields
            'browser_name' => 'nullable|string|max:50',
            'browser_version' => 'nullable|string|max:50',
            'operating_system' => 'nullable|string|max:100',
            'device_type' => 'nullable|string|max:50',
            'screen_resolution' => 'nullable|string|max:50',
            'viewport_size' => 'nullable|string|max:50',
            'technical_metadata' => 'nullable|array',
        ]);

        $submission = $form->submissions()->create([
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'submitter_name' => $validated['submitter_name'],
            'submitter_email' => $validated['submitter_email'],
            'page_url' => $validated['page_url'],
            'status' => 'new',
            'browser_name' => $validated['browser_name'] ?? null,
            'browser_version' => $validated['browser_version'] ?? null,
            'operating_system' => $validated['operating_system'] ?? null,
            'device_type' => $validated['device_type'] ?? null,
            'screen_resolution' => $validated['screen_resolution'] ?? null,
            'viewport_size' => $validated['viewport_size'] ?? null,
            'technical_metadata' => $validated['technical_metadata'] ?? null,
        ]);

        // Handle file attachments if provided
        if (!empty($validated['attachments']) && is_array($validated['attachments']) && count($validated['attachments']) > 0) {
            try {
                $storageService = null;
                
                // Check if R2 is configured
                if (config('filesystems.disks.r2.bucket')) {
                    try {
                        $storageService = app(CloudflareR2Service::class);
                        \Log::info('Using R2 storage for file uploads');
                    } catch (\Exception $e) {
                        \Log::warning('R2 service initialization failed, falling back to local storage: ' . $e->getMessage());
                    }
                }
                
                // Fall back to local storage if R2 is not available
                if (!$storageService) {
                    $storageService = app(LocalStorageService::class);
                    \Log::info('Using local storage for file uploads');
                }
                
                foreach ($validated['attachments'] as $attachment) {
                    if (!empty($attachment['data']) && !empty($attachment['name'])) {
                        $fileInfo = $storageService->uploadBase64File(
                            $attachment['data'],
                            $form->id,
                            $submission->id,
                            $attachment['name']
                        );

                        if ($fileInfo) {
                            $submission->attachments()->create($fileInfo);
                            \Log::info('File uploaded successfully: ' . $attachment['name']);
                        } else {
                            \Log::warning('Failed to upload file: ' . $attachment['name']);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail the submission
                \Log::error('Failed to upload attachments: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback. We will review it soon.',
        ])->header('Access-Control-Allow-Origin', '*');
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('UAT Form validation failed', [
                'errors' => $e->errors(),
                'token' => $token,
            ]);
            
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422)->header('Access-Control-Allow-Origin', '*');
        } catch (\Exception $e) {
            \Log::error('UAT Form submission error', [
                'error' => $e->getMessage(),
                'token' => $token,
            ]);
            
            return response()->json([
                'error' => 'An error occurred processing your submission',
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function widget(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return response('// UAT Widget: Invalid request - no token provided', 400)
                ->header('Content-Type', 'application/javascript');
        }

        $form = Form::where('embed_token', $token)->first();
        
        if (!$form) {
            return response('// UAT Widget: Invalid form token', 404)
                ->header('Content-Type', 'application/javascript');
        }

        // Check if form is active
        if (!$form->is_active) {
            return response('// UAT Widget: Form is not active', 403)
                ->header('Content-Type', 'application/javascript');
        }

        // Get the referrer domain
        $referrer = $request->header('Referer');
        if ($referrer) {
            $parsedUrl = parse_url($referrer);
            $domain = $parsedUrl['host'] ?? '';
            
            // Check if domain is allowed
            if (!$form->isDomainAllowed($domain)) {
                \Log::warning('UAT Widget: Domain not allowed', [
                    'form_id' => $form->id,
                    'domain' => $domain,
                    'allowed_domains' => $form->allowed_domains,
                ]);
                return response('// UAT Widget: Domain not authorized', 403)
                    ->header('Content-Type', 'application/javascript');
            }
        }

        $widgetPath = public_path('embed-files/widget.js.template');
        
        if (!file_exists($widgetPath)) {
            return response('// UAT Widget: Widget template not found at ' . $widgetPath, 404)
                ->header('Content-Type', 'application/javascript');
        }

        $content = file_get_contents($widgetPath);
        
        // Replace placeholders with form customization values
        $replacements = [
            '{{API_URL}}' => config('app.url') . '/api/submit/' . $token,
            '{{BUTTON_COLOR}}' => $form->button_color ?? '#3b82f6',
            '{{BUTTON_TEXT_COLOR}}' => $form->button_text_color ?? '#ffffff',
            '{{BUTTON_SIZE}}' => $form->button_size ?? 'medium',
            '{{BUTTON_POSITION}}' => $form->button_position ?? 'bottom-right',
            '{{BUTTON_TEXT}}' => $form->button_text ?? 'Report Issue',
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }
        
        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }
}