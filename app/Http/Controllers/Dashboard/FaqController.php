<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Faq\DestroyFaqAction;
use App\Actions\Admin\Faq\IndexFaqAction;
use App\Actions\Admin\Faq\RestoreFaqAction;
use App\Actions\Admin\Faq\StoreFaqAction;
use App\Actions\Admin\Faq\UpdateFaqAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request, IndexFaqAction $action): View
    {
        $data = $action->run($request);

        return view('admin.faqs.index', $data);
    }

    public function create(): View
    {
        return view('admin.faqs.create');
    }

    public function store(StoreFaqRequest $request, StoreFaqAction $action): RedirectResponse
    {
        $action->run($request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function show(Faq $faq): View
    {
        return view('admin.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(UpdateFaqRequest $request, Faq $faq, UpdateFaqAction $action): RedirectResponse
    {
        $action->run($faq, $request->validated());

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq, DestroyFaqAction $action): RedirectResponse
    {
        $action->run($faq);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted.');
    }

    public function restore(int $id, RestoreFaqAction $action): RedirectResponse
    {
        $action->run($id);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ restored.');
    }
}
