<?php

namespace App\Http\Controllers;

use App\Models\CtaButton;
use App\Models\CtaButtonRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CtaButtonRuleController extends Controller
{
    use AuthorizesRequests;
    /**
     * Store a new rule for a CTA button
     */
    public function store(Request $request, CtaButton $ctaButton)
    {
        $this->authorize('update', $ctaButton);

        $validated = $request->validate([
            'url_pattern' => 'required|string|max:500',
            'destination_url' => 'required|url|max:500',
            'pattern_description' => 'nullable|string|max:255',
            'priority' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rule = $ctaButton->rules()->create($validated);

        return redirect()->back()->with('success', 'URL rule added successfully');
    }

    /**
     * Update a rule
     */
    public function update(Request $request, CtaButton $ctaButton, CtaButtonRule $rule)
    {
        $this->authorize('update', $ctaButton);

        // Ensure the rule belongs to the button
        if ($rule->cta_button_id !== $ctaButton->id) {
            abort(403);
        }

        $validated = $request->validate([
            'url_pattern' => 'required|string|max:500',
            'destination_url' => 'required|url|max:500',
            'pattern_description' => 'nullable|string|max:255',
            'priority' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return redirect()->back()->with('success', 'URL rule updated successfully');
    }

    /**
     * Delete a rule
     */
    public function destroy(CtaButton $ctaButton, CtaButtonRule $rule)
    {
        $this->authorize('update', $ctaButton);

        // Ensure the rule belongs to the button
        if ($rule->cta_button_id !== $ctaButton->id) {
            abort(403);
        }

        $rule->delete();

        return redirect()->back()->with('success', 'URL rule deleted successfully');
    }

    /**
     * Test a URL pattern
     */
    public function test(Request $request, CtaButton $ctaButton)
    {
        $this->authorize('view', $ctaButton);

        $validated = $request->validate([
            'test_url' => 'required|string',
        ]);

        $testUrl = parse_url($validated['test_url'], PHP_URL_PATH) ?: '/';
        $matchedRule = null;

        foreach ($ctaButton->activeRules as $rule) {
            if ($rule->matchesUrl($testUrl)) {
                $matchedRule = $rule;
                break;
            }
        }

        if ($matchedRule) {
            return response()->json([
                'matched' => true,
                'rule' => $matchedRule,
                'destination_url' => $matchedRule->getDestinationUrl($testUrl),
            ]);
        }

        return response()->json([
            'matched' => false,
            'message' => 'No matching rule found',
        ]);
    }
}
