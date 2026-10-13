<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\StaticPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        return view('support.index');
    }

    public function faqs(Request $request): View
    {
        $query = Faq::active()->ordered();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $faqs = $query->get();
        $categories = Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();

        return view('support.faqs', [
            'faqs' => $faqs,
            'categories' => $categories,
            'selectedCategory' => $request->category,
            'searchQuery' => $request->search,
        ]);
    }

    public function policies(): View
    {
        $policies = StaticPage::published()
            ->whereIn('slug', ['policies', 'terms-and-conditions'])
            ->ordered()
            ->get();

        return view('support.policies', [
            'policies' => $policies,
        ]);
    }

    public function contact(Request $request): View
    {
        return view('support.contact', [
            'user' => $request->user(),
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['status'] = 'new';

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        ContactMessage::create($validated);

        return back()->with('success', trans('support.contact.submit_success'));
    }

    public function myMessages(Request $request): View
    {
        $user = $request->user();

        $messages = ContactMessage::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('support.messages', [
            'messages' => $messages,
        ]);
    }

    public function showMessage(Request $request, ContactMessage $contactMessage): View
    {
        $user = $request->user();

        if ($contactMessage->user_id !== $user->id && $contactMessage->email !== $user->email) {
            abort(404);
        }

        return view('support.message', [
            'message' => $contactMessage,
        ]);
    }
}
