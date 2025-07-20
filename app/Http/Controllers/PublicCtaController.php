<?php

namespace App\Http\Controllers;

use App\Models\CtaButton;
use App\Models\CtaButtonAnalytic;
use Illuminate\Http\Request;

class PublicCtaController extends Controller
{
    /**
     * Serve the CTA widget JavaScript
     */
    public function widget(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return response('// CTA Widget: Invalid request - no token provided', 400)
                ->header('Content-Type', 'application/javascript');
        }

        $button = CtaButton::where('embed_token', $token)->first();
        
        if (!$button) {
            return response('// CTA Widget: Invalid button token', 404)
                ->header('Content-Type', 'application/javascript');
        }

        // Check if button is active
        if (!$button->is_active) {
            return response('// CTA Widget: Button is not active', 403)
                ->header('Content-Type', 'application/javascript');
        }

        // Get the referrer domain
        $referrer = $request->header('Referer');
        if ($referrer) {
            $parsedUrl = parse_url($referrer);
            $domain = $parsedUrl['host'] ?? '';
            
            // Check if domain is allowed
            if (!$button->isDomainAllowed($domain)) {
                \Log::warning('CTA Widget: Domain not allowed', [
                    'button_id' => $button->id,
                    'domain' => $domain,
                    'allowed_domains' => $button->allowed_domains,
                ]);
                return response('// CTA Widget: Domain not authorized', 403)
                    ->header('Content-Type', 'application/javascript');
            }
        }

        $widgetPath = public_path('embed-files/cta-widget.js.template');
        
        if (!file_exists($widgetPath)) {
            // Create the template if it doesn't exist
            $this->createWidgetTemplate();
        }

        $content = file_get_contents($widgetPath);
        
        // Replace placeholders with button customization values
        $baseUrl = env('EMBED_BASE_URL', config('app.url'));
        $replacements = [
            '{{API_URL}}' => $baseUrl . '/api/cta/click/' . $token,
            '{{RULES_URL}}' => $baseUrl . '/api/cta/rules/' . $token,
            '{{BUTTON_COLOR}}' => $button->button_color,
            '{{BUTTON_TEXT_COLOR}}' => $button->button_text_color,
            '{{BUTTON_SIZE}}' => $button->button_size,
            '{{BUTTON_POSITION}}' => $button->button_position,
            '{{BUTTON_TEXT}}' => $button->button_text,
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }
        
        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Get active rules for a button
     */
    public function rules(Request $request, $token)
    {
        $button = CtaButton::where('embed_token', $token)->active()->first();
        
        if (!$button) {
            return response()->json(['error' => 'Invalid token'], 404)
                ->header('Access-Control-Allow-Origin', '*');
        }

        // Check domain
        $origin = $request->header('Origin');
        if ($origin) {
            $parsedUrl = parse_url($origin);
            $domain = $parsedUrl['host'] ?? '';
            
            if (!$button->isDomainAllowed($domain)) {
                return response()->json(['error' => 'Domain not authorized'], 403)
                    ->header('Access-Control-Allow-Origin', '*');
            }
        }

        $rules = $button->activeRules->map(function ($rule) {
            return [
                'id' => $rule->id,
                'pattern' => $rule->url_pattern,
                'destination' => $rule->destination_url,
                'priority' => $rule->priority,
            ];
        });

        return response()->json(['rules' => $rules])
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Track button click
     */
    public function trackClick(Request $request, $token)
    {
        $button = CtaButton::where('embed_token', $token)->active()->first();
        
        if (!$button) {
            return response()->json(['error' => 'Invalid token'], 404)
                ->header('Access-Control-Allow-Origin', '*');
        }

        $validated = $request->validate([
            'page_url' => 'required|string|max:500',
            'rule_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        // Get referrer domain
        $referrerDomain = null;
        $origin = $request->header('Origin');
        if ($origin) {
            $parsedUrl = parse_url($origin);
            $referrerDomain = $parsedUrl['host'] ?? null;
        }

        // Create or update analytics record
        $analytics = CtaButtonAnalytic::firstOrCreate(
            [
                'cta_button_id' => $button->id,
                'cta_button_rule_id' => $validated['rule_id'] ?? null,
                'page_url' => $validated['page_url'],
                'created_at' => now()->startOfDay(),
            ],
            [
                'referrer_domain' => $referrerDomain,
                'click_count' => 0,
                'metadata' => $validated['metadata'] ?? [],
            ]
        );

        $analytics->increment('click_count');

        return response()->json(['success' => true])
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Create the widget template if it doesn't exist
     */
    private function createWidgetTemplate()
    {
        $directory = public_path('embed-files');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $template = <<<'JS'
(function() {
    'use strict';

    // Widget configuration
    const API_URL = '{{API_URL}}';
    const RULES_URL = '{{RULES_URL}}';
    const WIDGET_ID = 'cta-widget-' + Math.random().toString(36).substr(2, 9);
    const BUTTON_COLOR = '{{BUTTON_COLOR}}';
    const BUTTON_TEXT_COLOR = '{{BUTTON_TEXT_COLOR}}';
    const BUTTON_SIZE = '{{BUTTON_SIZE}}';
    const BUTTON_POSITION = '{{BUTTON_POSITION}}';
    const BUTTON_TEXT = '{{BUTTON_TEXT}}';

    // Create styles
    const styles = `
        #${WIDGET_ID} {
            position: fixed;
            ${BUTTON_POSITION === 'bottom-right' ? 'bottom: 20px; right: 20px;' : ''}
            ${BUTTON_POSITION === 'bottom-left' ? 'bottom: 20px; left: 20px;' : ''}
            ${BUTTON_POSITION === 'top-right' ? 'top: 20px; right: 20px;' : ''}
            ${BUTTON_POSITION === 'top-left' ? 'top: 20px; left: 20px;' : ''}
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: none;
        }

        #${WIDGET_ID} .cta-btn {
            background: ${BUTTON_COLOR};
            color: ${BUTTON_TEXT_COLOR};
            border: none;
            padding: ${BUTTON_SIZE === 'small' ? '8px 16px' : BUTTON_SIZE === 'large' ? '16px 32px' : '12px 24px'};
            border-radius: 30px;
            cursor: pointer;
            font-size: ${BUTTON_SIZE === 'small' ? '14px' : BUTTON_SIZE === 'large' ? '18px' : '16px'};
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        #${WIDGET_ID} .cta-btn:hover {
            filter: brightness(90%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
    `;

    // Inject styles
    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);

    // Create widget container
    const widgetContainer = document.createElement('div');
    widgetContainer.id = WIDGET_ID;
    document.body.appendChild(widgetContainer);

    // Variables
    let rules = [];
    let matchedRule = null;

    // Fetch rules from API
    async function fetchRules() {
        try {
            const response = await fetch(RULES_URL, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.rules) {
                rules = data.rules;
                checkUrlAndShowButton();
            }
        } catch (error) {
            console.error('CTA Widget: Failed to fetch rules', error);
        }
    }

    // Check if current URL matches any rule
    function checkUrlAndShowButton() {
        const currentPath = window.location.pathname;
        
        // Sort rules by priority (highest first)
        const sortedRules = [...rules].sort((a, b) => b.priority - a.priority);
        
        for (const rule of sortedRules) {
            if (matchesPattern(currentPath, rule.pattern)) {
                matchedRule = rule;
                showButton(rule);
                break;
            }
        }
    }

    // Pattern matching function
    function matchesPattern(url, pattern) {
        // Convert pattern to regex
        let regexPattern = pattern;
        
        // If pattern doesn't start with ^ or /, treat it as a simple pattern
        if (!/^[\^\/]/.test(pattern)) {
            // Escape special regex characters except parentheses
            regexPattern = pattern.replace(/[.*+?^${}|[\]\\]/g, '\\$&');
            // Replace escaped parentheses back to capture groups
            regexPattern = regexPattern.replace(/\\\(/g, '(').replace(/\\\)/g, ')');
            // Add anchors
            regexPattern = '^' + regexPattern + '$';
        }
        
        try {
            const regex = new RegExp(regexPattern);
            return regex.test(url);
        } catch (e) {
            console.error('CTA Widget: Invalid pattern', pattern, e);
            return false;
        }
    }

    // Get destination URL with parameters replaced
    function getDestinationUrl(sourceUrl, pattern, destination) {
        let regexPattern = pattern;
        
        if (!/^[\^\/]/.test(pattern)) {
            regexPattern = pattern.replace(/[.*+?^${}|[\]\\]/g, '\\$&');
            regexPattern = regexPattern.replace(/\\\(/g, '(').replace(/\\\)/g, ')');
            regexPattern = '^' + regexPattern + '$';
        }
        
        try {
            const regex = new RegExp(regexPattern);
            const matches = sourceUrl.match(regex);
            
            if (matches) {
                let finalUrl = destination;
                for (let i = 1; i < matches.length; i++) {
                    finalUrl = finalUrl.replace('$' + i, matches[i]);
                }
                return finalUrl;
            }
        } catch (e) {
            console.error('CTA Widget: Error processing destination URL', e);
        }
        
        return destination;
    }

    // Show the button
    function showButton(rule) {
        const button = document.createElement('a');
        button.className = 'cta-btn';
        button.textContent = BUTTON_TEXT;
        button.target = '_blank';
        button.rel = 'noopener noreferrer';
        
        const destinationUrl = getDestinationUrl(window.location.pathname, rule.pattern, rule.destination);
        button.href = destinationUrl;
        
        // Track click
        button.addEventListener('click', () => {
            trackClick(rule.id);
        });
        
        widgetContainer.appendChild(button);
        widgetContainer.style.display = 'block';
    }

    // Track button click
    async function trackClick(ruleId) {
        try {
            await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    page_url: window.location.href,
                    rule_id: ruleId,
                    metadata: {
                        user_agent: navigator.userAgent,
                        screen_resolution: `${screen.width}x${screen.height}`,
                        viewport_size: `${window.innerWidth}x${window.innerHeight}`,
                        timestamp: new Date().toISOString(),
                    }
                })
            });
        } catch (error) {
            console.error('CTA Widget: Failed to track click', error);
        }
    }

    // Initialize
    fetchRules();

    // Listen for URL changes (for SPAs)
    let lastUrl = window.location.href;
    new MutationObserver(() => {
        const url = window.location.href;
        if (url !== lastUrl) {
            lastUrl = url;
            widgetContainer.innerHTML = '';
            widgetContainer.style.display = 'none';
            matchedRule = null;
            checkUrlAndShowButton();
        }
    }).observe(document, { subtree: true, childList: true });
})();
JS;

        file_put_contents(public_path('embed-files/cta-widget.js.template'), $template);
    }
}
