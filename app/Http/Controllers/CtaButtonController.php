<?php

namespace App\Http\Controllers;

use App\Models\CtaButton;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CtaButtonController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buttons = auth()->user()->ctaButtons()
            ->withCount(['rules', 'analytics'])
            ->latest()
            ->get()
            ->map(function ($button) {
                return [
                    'id' => $button->id,
                    'button_name' => $button->button_name,
                    'button_text' => $button->button_text,
                    'embed_token' => $button->embed_token,
                    'rules_count' => $button->rules_count,
                    'analytics_count' => $button->analytics_count,
                    'created_at' => $button->created_at->toISOString(),
                    'is_active' => $button->is_active,
                ];
            });

        return Inertia::render('CtaButtons/Index', [
            'buttons' => $buttons
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('CtaButtons/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'button_name' => 'required|string|max:255',
            'allowed_domains' => 'required|array|min:1',
            'allowed_domains.*' => 'required|string',
            'button_color' => 'required|string|max:7',
            'button_text_color' => 'required|string|max:7',
            'button_size' => 'required|in:small,medium,large',
            'button_position' => 'required|in:top-left,top-right,bottom-left,bottom-right',
            'button_text' => 'required|string|max:50',
        ]);

        $button = auth()->user()->ctaButtons()->create($validated);

        return redirect()->route('cta-buttons.index')
            ->with('success', 'CTA button created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CtaButton $ctaButton)
    {
        $this->authorize('view', $ctaButton);

        $ctaButton->load(['rules' => function ($query) {
            $query->orderBy('priority', 'desc');
        }, 'analytics' => function ($query) {
            $query->latest()->limit(100);
        }]);

        return Inertia::render('CtaButtons/Show', [
            'button' => $ctaButton,
            'embedCode' => $this->generateEmbedCode($ctaButton)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CtaButton $ctaButton)
    {
        $this->authorize('update', $ctaButton);

        $ctaButton->load(['rules' => function ($query) {
            $query->orderBy('priority', 'desc');
        }]);

        return Inertia::render('CtaButtons/Edit', [
            'button' => [
                'id' => $ctaButton->id,
                'button_name' => $ctaButton->button_name,
                'button_text' => $ctaButton->button_text,
                'button_color' => $ctaButton->button_color,
                'button_text_color' => $ctaButton->button_text_color,
                'button_size' => $ctaButton->button_size,
                'button_position' => $ctaButton->button_position,
                'allowed_domains' => $ctaButton->allowed_domains,
                'embed_token' => $ctaButton->embed_token,
                'is_active' => $ctaButton->is_active,
                'rules' => $ctaButton->rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'url_pattern' => $rule->url_pattern,
                        'destination_url' => $rule->destination_url,
                        'pattern_description' => $rule->pattern_description,
                        'priority' => $rule->priority,
                        'is_active' => $rule->is_active,
                        'clicks_count' => $rule->clicks_count ?? 0,
                    ];
                }),
            ],
            'embedCode' => $this->generateEmbedCode($ctaButton)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CtaButton $ctaButton)
    {
        $this->authorize('update', $ctaButton);

        $validated = $request->validate([
            'button_name' => 'required|string|max:255',
            'allowed_domains' => 'required|array|min:1',
            'allowed_domains.*' => 'required|string',
            'button_color' => 'required|string|max:7',
            'button_text_color' => 'required|string|max:7',
            'button_size' => 'required|in:small,medium,large',
            'button_position' => 'required|in:top-left,top-right,bottom-left,bottom-right',
            'button_text' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $ctaButton->update($validated);

        return redirect()->back()->with('success', 'CTA button updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CtaButton $ctaButton)
    {
        $this->authorize('delete', $ctaButton);

        $ctaButton->delete();

        return redirect()->route('cta-buttons.index')
            ->with('success', 'CTA button deleted successfully');
    }

    /**
     * Toggle the active status of a CTA button
     */
    public function toggleStatus(CtaButton $ctaButton)
    {
        $this->authorize('update', $ctaButton);

        $ctaButton->update([
            'is_active' => !$ctaButton->is_active
        ]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    /**
     * Generate embed code for the CTA button
     */
    private function generateEmbedCode(CtaButton $button)
    {
        $baseUrl = config('app.url');
        return '<script src="' . $baseUrl . '/embed/cta-widget.js?token=' . $button->embed_token . '"></script>';
    }
}
