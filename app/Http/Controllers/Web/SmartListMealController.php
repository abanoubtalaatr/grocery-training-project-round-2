<?php

namespace App\Http\Controllers\Web;

use App\Actions\Api\SmartListMealAction;
use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\SmartList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SmartListMealController extends Controller
{
    public function store(Request $request, SmartList $smartList, SmartListMealAction $action): RedirectResponse
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'meal_id' => ['required', 'integer', 'exists:meals,id'],
        ]);

        $data = \App\DTOs\Api\SmartListMealData::fromValidated($validated);

        $result = $action->execute($smartList, $data);

        $message = $result->isNew
            ? trans('smart_lists.meal_added')
            : trans('smart_lists.meal_already_exists');

        return back()->with('success', $message);
    }

    public function destroy(Request $request, SmartList $smartList, Meal $meal, SmartListMealAction $action): RedirectResponse
    {
        if ($smartList->user_id !== $request->user()->id) {
            abort(404);
        }

        $result = $action->remove($smartList, $meal->id);

        if ($result === null) {
            return back()->with('error', trans('smart_lists.meal_not_found'));
        }

        return back()->with('success', trans('smart_lists.meal_removed'));
    }
}
