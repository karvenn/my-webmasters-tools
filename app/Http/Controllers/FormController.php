<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $forms = Auth::user()->forms()
            ->withCount(['submissions', 'submissions as new_submissions_count' => function ($query) {
                $query->where('status', 'new');
            }])
            ->latest()
            ->get();

        return Inertia::render('forms/Index', [
            'forms' => $forms,
        ]);
    }

    public function create()
    {
        return Inertia::render('forms/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'website_name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'button_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'button_text_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'button_size' => ['nullable', Rule::in(['small', 'medium', 'large'])],
            'button_position' => ['nullable', Rule::in(['bottom-right', 'bottom-left', 'top-right', 'top-left'])],
            'button_text' => 'nullable|string|max:50',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'required|string',
        ]);

        // Set default allowed domain if not provided
        if (empty($validated['allowed_domains'])) {
            $parsedUrl = parse_url($validated['website_url']);
            $validated['allowed_domains'] = [$parsedUrl['host'] ?? ''];
        }

        $form = Auth::user()->forms()->create($validated);

        return redirect()->route('forms.index')
            ->with('success', 'Form created successfully!');
    }

    public function show(Form $form)
    {
        $this->authorize('view', $form);

        $form->load(['submissions' => function ($query) {
            $query->with('attachments')->latest();
        }]);

        return Inertia::render('forms/Submissions', [
            'form' => $form,
            'submissions' => $form->submissions,
            'embedCode' => $this->generateEmbedCode($form),
        ]);
    }

    public function edit(Form $form)
    {
        $this->authorize('update', $form);

        return Inertia::render('forms/Edit', [
            'form' => $form->only([
                'id',
                'website_name',
                'website_url',
                'is_active',
                'button_color',
                'button_text_color',
                'button_size',
                'button_position',
                'button_text',
                'allowed_domains',
            ]),
        ]);
    }

    public function update(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $validated = $request->validate([
            'website_name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'button_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'button_text_color' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'button_size' => ['nullable', Rule::in(['small', 'medium', 'large'])],
            'button_position' => ['nullable', Rule::in(['bottom-right', 'bottom-left', 'top-right', 'top-left'])],
            'button_text' => 'nullable|string|max:50',
            'allowed_domains' => 'nullable|array',
            'allowed_domains.*' => 'required|string',
        ]);

        // Set default allowed domain if not provided
        if (empty($validated['allowed_domains'])) {
            $parsedUrl = parse_url($validated['website_url']);
            $validated['allowed_domains'] = [$parsedUrl['host'] ?? ''];
        }

        $form->update($validated);

        return redirect()->route('forms.index')
            ->with('success', 'Form updated successfully!');
    }

    public function toggleStatus(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $form->update([
            'is_active' => !$form->is_active,
        ]);

        $status = $form->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Form {$status} successfully!");
    }

    public function destroy(Form $form)
    {
        $this->authorize('delete', $form);

        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Form deleted successfully!');
    }

    protected function generateEmbedCode(Form $form): string
    {
        $baseUrl = config('app.url');
        return '<script src="' . $baseUrl . '/embed/widget.js?token=' . $form->embed_token . '"></script>';
    }
}