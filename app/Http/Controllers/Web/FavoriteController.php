<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Meal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()->favorites()
            ->with(['meal.category', 'meal.subcategory'])
            ->latest()
            ->paginate(12);

        return view('favorites.index', [
            'favorites' => $favorites,
        ]);
    }

    public function toggle(Request $request, Meal $meal): RedirectResponse
    {
        $user = $request->user();

        DB::beginTransaction();

        $favorite = $user->favorites()->where('meal_id', $meal->id)->first();

        if ($favorite) {
            $favorite->delete();
            $message = trans('favorites.removed');
        } else {
            $user->favorites()->create([
                'meal_id' => $meal->id,
            ]);
            $message = trans('favorites.added');
        }

        DB::commit();

        return back()->with('success', $message);
    }

    public function remove(Request $request, Meal $meal): RedirectResponse
    {
        $user = $request->user();

        $deleted = $user->favorites()->where('meal_id', $meal->id)->delete();

        if (! $deleted) {
            return back()->with('error', trans('favorites.not_found'));
        }

        return back()->with('success', trans('favorites.removed'));
    }
}
