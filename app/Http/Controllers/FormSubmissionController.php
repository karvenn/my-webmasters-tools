<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FormSubmissionController extends Controller
{
    use AuthorizesRequests;
    public function updateStatus(Form $form, FormSubmission $submission, Request $request)
    {
        $this->authorize('update', $form);

        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => 'required|in:new,wip,agency_review,client_review,on_hold,concluded',
        ]);

        $submission->update($validated);

        return back()->with('success', 'Submission status updated successfully!');
    }

    public function destroy(Form $form, FormSubmission $submission)
    {
        $this->authorize('delete', $form);

        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->delete();

        return back()->with('success', 'Submission deleted successfully!');
    }
}