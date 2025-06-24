<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        ]);

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