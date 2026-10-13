<?php

namespace App\Http\Controllers\Web;

use App\Actions\Api\AddSmartListToCartAction;
use App\Actions\Api\SmartListAction;
use App\Http\Controllers\Controller;
use App\Models\SmartList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartListController extends Controller
{
    public function index(Request $request): View
    {
        $smartLists = $request->user()->smartLists()
            ->withCount('meals')
            ->with('meals')
            ->latest()
            ->get();

        return view('smart-lists.index', [
            'smartLists' => $smartLists,
        ]);
    }

    public function create(): View
    {
        return view('smart-lists.create');
    }

    public function store(Request $request, SmartListAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'notify_on_price_drop' => ['nullable', 'boolean'],
            'notify_on_offers' => ['nullable', 'boolean'],
        ]);

        $validated['notify_on_price_drop'] = $validated['notify_on_price_drop'] ?? true;
        $validated['notify_on_offers'] = $validated['notify_on_offers'] ?? true;

        $data = \App\DTOs\Api\CreateSmartListData::fromValidated($validated);

        $action->create($request->user(), $data);

        return redirect()->route('smart-lists.index')
            ->with('success', trans('smart_lists.create_success'));
    }

    public function show(Request $request, SmartList $smartList): View
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $smartList->load('meals.category', 'meals.subcategory');
        $smartList->loadCount('meals');

        $userFavoriteIds = $request->user()->favorites()->pluck('meal_id')->toArray();

        return view('smart-lists.show', [
            'smartList' => $smartList,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }

    public function edit(Request $request, SmartList $smartList): View
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        return view('smart-lists.edit', [
            'smartList' => $smartList,
        ]);
    }

    public function update(Request $request, SmartList $smartList, SmartListAction $action): RedirectResponse
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'notify_on_price_drop' => ['nullable', 'boolean'],
            'notify_on_offers' => ['nullable', 'boolean'],
        ]);

        $validated['notify_on_price_drop'] = $validated['notify_on_price_drop'] ?? false;
        $validated['notify_on_offers'] = $validated['notify_on_offers'] ?? false;

        $data = \App\DTOs\Api\UpdateSmartListData::fromValidated($validated);

        $action->update($smartList, $data);

        return redirect()->route('smart-lists.show', $smartList->id)
            ->with('success', trans('smart_lists.update_success'));
    }

    public function destroy(Request $request, SmartList $smartList, SmartListAction $action): RedirectResponse
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $action->delete($smartList);

        return redirect()->route('smart-lists.index')
            ->with('success', trans('smart_lists.delete_success'));
    }

    public function addToCart(Request $request, SmartList $smartList, AddSmartListToCartAction $action): RedirectResponse
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $result = $action->execute($request->user(), $smartList);

        $added = $result['added_count'];
        $skipped = $result['skipped_count'];

        if ($added > 0 && $skipped > 0) {
            $message = trans('smart_lists.add_to_cart_partial', ['added' => $added, 'skipped' => $skipped]);
        } elseif ($added > 0) {
            $message = trans('smart_lists.add_to_cart_success', ['count' => $added]);
        } else {
            $message = trans('smart_lists.add_to_cart_empty');
        }

        return redirect()->route('cart.show')->with('success', $message);
    }
}
